<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\CashMovement;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['pos.discounts_enabled' => true]);
    }

    public function test_staff_can_open_the_order_board(): void
    {
        [$branch, $staff] = $this->makeStaff();

        $this->actingAs($staff)
            ->get(route('staff.pos.index'))
            ->assertOk()
            ->assertSee('Papan Pesanan')
            ->assertSee('Nama pelanggan')
            ->assertSee($branch->name);
    }

    public function test_creating_an_order_assigns_a_daily_queue_number(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 09:00:00'));

        [$branch, $staff] = $this->makeStaff();

        $this->actingAs($staff)
            ->post(route('staff.pos.store'), [
                'customer_name' => 'Budi',
                'order_type' => Order::ORDER_TYPE_DINE_IN,
            ])
            ->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame(Order::STATUS_OPEN, $order->status);
        $this->assertSame('Budi', $order->customer_name);
        $this->assertSame(Order::ORDER_TYPE_DINE_IN, $order->order_type);
        $this->assertSame(1, $order->queue_number);
        $this->assertSame('#001', $order->queueLabel());
        $this->assertSame('2026-11-10', $order->business_date->toDateString());
        $this->assertSame('WJT-'.$branch->id.'-20261110-001', $order->order_code);
        $this->assertSame($branch->id, $order->branch_id);
        $this->assertSame($staff->id, $order->created_by);

        $this->assertDatabaseHas('order_edit_logs', [
            'order_id' => $order->id,
            'user_id' => $staff->id,
            'action_type' => 'created',
        ]);
    }

    public function test_order_requires_customer_name_and_type(): void
    {
        [, $staff] = $this->makeStaff();

        $this->actingAs($staff)
            ->post(route('staff.pos.store'), [])
            ->assertSessionHasErrors(['customer_name', 'order_type']);

        $this->assertSame(0, Order::count());
    }

    public function test_queue_numbers_are_per_branch_and_never_reused(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 09:00:00'));

        [$branchA, $staffA] = $this->makeStaff('Cabang A');
        [, $staffB] = $this->makeStaff('Cabang B');

        $first = $this->createOrder($staffA, 'Budi');
        $second = $this->createOrder($staffA, 'Siti');
        $this->createOrder($staffB, 'Andi');

        $this->assertSame(1, $first->queue_number);
        $this->assertSame(2, $second->queue_number);
        $this->assertSame(1, Order::where('branch_id', '!=', $branchA->id)->firstOrFail()->queue_number);

        $this->actingAs($staffA)
            ->post(route('staff.orders.cancel', $first))
            ->assertRedirect(route('staff.pos.index'));

        $third = $this->createOrder($staffA, 'Dewi');

        $this->assertSame(3, $third->queue_number);
        $this->assertSame('#003', $third->queueLabel());

        $this->travelTo(Carbon::parse('2026-11-11 09:00:00'));

        $nextDay = $this->createOrder($staffA, 'Eko');

        $this->assertSame(1, $nextDay->queue_number);
        $this->assertSame('2026-11-11', $nextDay->business_date->toDateString());
    }

    public function test_multiple_orders_can_be_active_at_the_same_time(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 20);

        $budi = $this->createOrder($staff, 'Budi');
        $siti = $this->createOrder($staff, 'Siti');

        $this->addItem($staff, $budi, $variant, 2);
        $this->addItem($staff, $siti, $variant, 1);

        $this->actingAs($staff)
            ->get(route('staff.pos.index'))
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('Siti')
            ->assertSee('#001')
            ->assertSee('#002');

        $this->assertSame(2, Order::whereIn('status', Order::ACTIVE_STATUSES)->count());
    }

    public function test_board_can_search_by_customer_name_and_queue_number(): void
    {
        [$branch, $staff] = $this->makeStaff();
        $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->createOrder($staff, 'Budi');
        $this->createOrder($staff, 'Siti');

        $this->flushSession();

        $this->actingAs($staff)
            ->get(route('staff.pos.index', ['search' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('#001')
            ->assertDontSee('#002');

        $this->actingAs($staff)
            ->get(route('staff.pos.index', ['search' => '002']))
            ->assertOk()
            ->assertSee('Siti')
            ->assertSee('#002')
            ->assertDontSee('#001');
    }

    public function test_adding_a_food_item_deducts_stock_and_applies_topping(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 2, true);

        $order->refresh();

        $this->assertSame('24000.00', $order->subtotal);
        $this->assertSame('24000.00', $order->total_amount);
        $this->assertSame(8, $this->stock($branch, $variant));

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_variant_id' => $variant->id,
            'unit_price' => 10000,
            'topping_price' => 2000,
            'quantity' => 2,
            'is_addon' => false,
            'line_total' => 24000,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'user_id' => $staff->id,
            'movement_type' => 'out',
            'quantity' => 2,
        ]);
    }

    public function test_topping_is_ignored_for_drinks(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Es Teh', Menu::CATEGORY_MINUMAN, 5000, 10, 'Es Teh');

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1, true);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_variant_id' => $variant->id,
            'topping_price' => 0,
            'line_total' => 5000,
        ]);
    }

    public function test_insufficient_stock_blocks_adding_item_without_changing_data(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 1);

        $order = $this->createOrder($staff);

        $this->actingAs($staff)
            ->post(route('staff.orders.items.store', $order), [
                'variant_id' => $variant->id,
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('stock');

        $this->assertSame(0, $order->items()->count());
        $this->assertSame(1, $this->stock($branch, $variant));
        $this->assertSame(0, StockMovement::count());
    }

    public function test_staff_can_apply_discount_and_notes_before_payment(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 2);

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), [
                'discount_amount' => 4000,
                'reason' => 'Promo pelanggan',
            ])
            ->assertRedirect(route('staff.pos.show', $order));

        $this->actingAs($staff)
            ->patch(route('staff.orders.notes', $order), [
                'notes' => 'Tanpa saus',
            ])
            ->assertRedirect(route('staff.pos.show', $order));

        $order->refresh();

        $this->assertSame('20000.00', $order->subtotal);
        $this->assertSame('4000.00', $order->discount_amount);
        $this->assertSame('16000.00', $order->total_amount);
        $this->assertSame('Tanpa saus', $order->notes);

        $this->assertDatabaseHas('order_edit_logs', [
            'order_id' => $order->id,
            'action_type' => 'discount_changed',
            'reason' => 'Promo pelanggan',
        ]);
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), [
                'discount_amount' => 15000,
            ])
            ->assertSessionHasErrors('discount_amount');

        $this->assertSame('0.00', $order->fresh()->discount_amount);
    }

    public function test_partial_then_full_payment_updates_payment_status(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'cash',
                'amount' => 4000,
                'amount_tendered' => 5000,
            ])
            ->assertRedirect(route('staff.orders.show', $order));

        $order->refresh();

        $this->assertSame('partial', $order->paymentStatus());
        $this->assertSame(4000.0, $order->paidAmount());
        $this->assertSame(6000.0, $order->balanceDue());
        $this->assertNull($order->paid_at);
        $this->assertTrue($order->isActive());

        $firstPayment = Payment::firstOrFail();
        $this->assertSame($staff->id, $firstPayment->received_by);
        $this->assertSame('cash', $firstPayment->payment_method);
        $this->assertSame('4000.00', $firstPayment->amount);
        $this->assertSame('5000.00', $firstPayment->amount_tendered);
        $this->assertSame('1000.00', $firstPayment->change_amount);
        $this->assertSame(Payment::STATUS_PAID, $firstPayment->status);

        $this->assertDatabaseHas('cash_movements', [
            'branch_id' => $branch->id,
            'user_id' => $staff->id,
            'movement_type' => 'in',
            'category' => CashMovement::CATEGORY_SALE,
            'amount' => 4000,
            'related_payment_id' => $firstPayment->id,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'qris',
                'amount' => 6000,
                'reference_code' => 'REF-123',
            ])
            ->assertRedirect(route('staff.orders.show', $order));

        $order->refresh();

        $this->assertSame('paid', $order->paymentStatus());
        $this->assertSame(10000.0, $order->paidAmount());
        $this->assertSame(0.0, $order->balanceDue());
        $this->assertNotNull($order->paid_at);
        $this->assertSame(2, Payment::count());
        $this->assertSame(1, CashMovement::count());

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'amount' => 6000,
            'reference_code' => 'REF-123',
        ]);
    }

    public function test_payment_amount_cannot_exceed_the_balance(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'qris',
                'amount' => 15000,
            ])
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, Payment::count());
        $this->assertSame('unpaid', $order->fresh()->paymentStatus());
    }

    public function test_cash_payment_requires_enough_tendered_cash(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'cash',
                'amount' => 10000,
                'amount_tendered' => 5000,
            ])
            ->assertSessionHasErrors('amount_tendered');

        $this->assertSame(0, Payment::count());
        $this->assertSame(0, CashMovement::count());
    }

    public function test_empty_order_cannot_be_paid(): void
    {
        [, $staff] = $this->makeStaff();

        $order = $this->createOrder($staff);

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'cash',
                'amount' => 10000,
                'amount_tendered' => 10000,
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Payment::count());
    }

    public function test_order_cannot_be_completed_before_it_is_fully_paid(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->from(route('staff.pos.show', $order))
            ->patch(route('staff.orders.status', $order), ['status' => Order::STATUS_COMPLETED])
            ->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_OPEN, $order->fresh()->status);
        $this->assertNull($order->fresh()->completed_at);
    }

    public function test_status_flow_and_completion_lock_the_order(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'qris',
                'amount' => 10000,
            ])
            ->assertRedirect();

        foreach ([Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_COMPLETED] as $status) {
            $this->actingAs($staff)
                ->from(route('staff.pos.show', $order))
                ->patch(route('staff.orders.status', $order), ['status' => $status])
                ->assertSessionHasNoErrors();

            $this->assertSame($status, $order->fresh()->status);
        }

        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertTrue($order->fresh()->isCompleted());

        $this->actingAs($staff)
            ->from(route('staff.orders.show', $order))
            ->patch(route('staff.orders.status', $order), ['status' => Order::STATUS_PROCESSING])
            ->assertSessionHasErrors('status');

        $this->actingAs($staff)
            ->post(route('staff.orders.pay', $order), [
                'payment_method' => 'qris',
                'amount' => 1000,
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAs($staff)
            ->post(route('staff.orders.items.store', $order), [
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('items');

        $this->actingAs($staff)
            ->post(route('staff.orders.cancel', $order))
            ->assertSessionHasErrors('order');
    }

    public function test_items_can_be_added_after_payment_as_addons_and_old_items_are_locked(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $food] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);
        [, $drink] = $this->makeMenu($branch, 'Es Teh', Menu::CATEGORY_MINUMAN, 5000, 10, 'Es Teh');

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $food, 1);

        $this->actingAs($staff)->post(route('staff.orders.pay', $order), [
            'payment_method' => 'qris',
            'amount' => 10000,
        ])->assertRedirect();

        $this->actingAs($staff)
            ->from(route('staff.pos.show', $order))
            ->patch(route('staff.orders.status', $order), ['status' => Order::STATUS_PROCESSING]);

        $this->actingAs($staff)
            ->from(route('staff.pos.show', $order))
            ->patch(route('staff.orders.status', $order), ['status' => Order::STATUS_READY]);

        $this->assertSame(Order::STATUS_READY, $order->fresh()->status);

        $this->addItem($staff, $order, $drink, 1);

        $order->refresh();

        $this->assertSame(Order::STATUS_PROCESSING, $order->status);
        $this->assertSame('15000.00', $order->total_amount);
        $this->assertSame('partial', $order->paymentStatus());
        $this->assertSame(9, $this->stock($branch, $drink));

        $oldItem = $order->items()->where('menu_variant_id', $food->id)->firstOrFail();
        $newItem = $order->items()->where('menu_variant_id', $drink->id)->firstOrFail();

        $this->assertFalse($oldItem->is_addon);
        $this->assertTrue($newItem->is_addon);

        $this->actingAs($staff)
            ->patch(route('staff.orders.items.update', [$order, $oldItem]), [
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('items');

        $this->actingAs($staff)
            ->delete(route('staff.orders.items.destroy', [$order, $oldItem]))
            ->assertSessionHasErrors('items');

        $this->assertSame(1, $oldItem->fresh()->quantity);
    }

    public function test_cancelling_an_order_restores_stock_and_blocks_reuse_of_number(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 2);

        $this->assertSame(8, $this->stock($branch, $variant));

        $this->actingAs($staff)
            ->post(route('staff.orders.cancel', $order))
            ->assertRedirect(route('staff.pos.index'));

        $order->refresh();

        $this->assertSame(Order::STATUS_CANCELLED, $order->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(10, $this->stock($branch, $variant));

        $this->assertDatabaseHas('order_edit_logs', [
            'order_id' => $order->id,
            'action_type' => 'cancelled',
        ]);

        $next = $this->createOrder($staff, 'Pelanggan Baru');
        $this->assertSame(2, $next->queue_number);
    }

    public function test_order_with_payment_cannot_be_cancelled(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)->post(route('staff.orders.pay', $order), [
            'payment_method' => 'qris',
            'amount' => 5000,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.orders.cancel', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame(Order::STATUS_OPEN, $order->fresh()->status);
        $this->assertSame(9, $this->stock($branch, $variant));
        $this->assertSame(1, Payment::count());
    }

    public function test_updating_and_removing_items_adjusts_stock(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff);
        $this->addItem($staff, $order, $variant, 1);

        $item = $order->items()->firstOrFail();
        $this->assertSame(9, $this->stock($branch, $variant));

        $this->actingAs($staff)
            ->patch(route('staff.orders.items.update', [$order, $item]), [
                'quantity' => 3,
                'with_topping' => '1',
            ])
            ->assertRedirect(route('staff.pos.show', $order));

        $this->assertSame(7, $this->stock($branch, $variant));
        $this->assertSame('36000.00', $order->fresh()->total_amount);
        $this->assertSame(3, $item->fresh()->quantity);

        $this->actingAs($staff)
            ->delete(route('staff.orders.items.destroy', [$order, $item]))
            ->assertRedirect(route('staff.pos.show', $order));

        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
        $this->assertSame(10, $this->stock($branch, $variant));
        $this->assertSame('0.00', $order->fresh()->total_amount);
    }

    public function test_staff_in_the_same_branch_can_view_and_edit_each_others_orders(): void
    {
        [$branch, $staff] = $this->makeStaff('Cabang A');
        $colleague = User::factory()->create(['branch_id' => $branch->id]);

        [$otherBranch, $otherStaff] = $this->makeStaff('Cabang B');

        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 20);
        $this->makeMenu($otherBranch, 'Menu Lain', Menu::CATEGORY_MAKANAN, 5000, 10);

        $order = $this->createOrder($staff, 'Budi');
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($colleague)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertSee('Budi');

        $this->actingAs($colleague)
            ->post(route('staff.orders.items.store', $order), [
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('staff.pos.show', $order));

        $this->assertSame(2, (int) $order->items()->sum('quantity'));

        $this->actingAs($colleague)
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertSee($order->order_code);

        $this->actingAs($otherStaff)
            ->get(route('staff.pos.show', $order))
            ->assertForbidden();

        $this->actingAs($otherStaff)
            ->post(route('staff.orders.items.store', $order), [
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($otherStaff)
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertDontSee($order->order_code);
    }

    public function test_receipt_shows_queue_customer_and_payment_history(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff, 'Budi');
        $this->addItem($staff, $order, $variant, 1, true);

        $this->actingAs($staff)->post(route('staff.orders.pay', $order), [
            'payment_method' => 'cash',
            'amount' => 5000,
            'amount_tendered' => 10000,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.orders.show', $order))
            ->assertOk()
            ->assertSee('#001')
            ->assertSee('Budi')
            ->assertSee('Dine-in')
            ->assertSee('Riwayat Pembayaran')
            ->assertSee('Tunai')
            ->assertSee('Kembalian')
            ->assertSee('Sisa Tagihan')
            ->assertSee('Rp 7.000');
    }

    public function test_cancelled_order_pages_do_not_show_payment_status_or_outstanding_balance(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff, 'Budi');
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)
            ->post(route('staff.orders.cancel', $order))
            ->assertRedirect();

        $this->actingAs($staff)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertSee('Rincian Pesanan')
            ->assertSee('Tidak ada (dibatalkan)')
            ->assertDontSee('Sisa tagihan')
            ->assertDontSee('Item lama terkunci karena sudah ada pembayaran');

        $this->actingAs($staff)
            ->get(route('staff.orders.show', $order))
            ->assertOk()
            ->assertSee('Tidak ada (dibatalkan)')
            ->assertDontSee('Status Bayar')
            ->assertDontSee('Sisa Tagihan');

        $this->actingAs($staff)
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Dibatalkan, tidak ada tagihan');

        $this->actingAs($staff)
            ->get(route('staff.pos.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee('Dibatalkan, tidak ada tagihan');

        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.orders.index'))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Dibatalkan, tidak ada tagihan');

        $this->actingAs($owner)
            ->get(route('owner.orders.show', $order))
            ->assertOk()
            ->assertDontSee('Status Bayar')
            ->assertDontSee('Sisa Tagihan');
    }

    public function test_owner_can_view_all_orders_with_revenue_based_on_payments(): void
    {
        [$branch, $staff] = $this->makeStaff();
        $owner = User::factory()->owner()->create();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 20);

        $paidOrder = $this->createOrder($staff, 'Budi');
        $this->addItem($staff, $paidOrder, $variant, 1);

        $this->actingAs($staff)->post(route('staff.orders.pay', $paidOrder), [
            'payment_method' => 'cash',
            'amount' => 10000,
            'amount_tendered' => 10000,
        ]);

        $unpaidOrder = $this->createOrder($staff, 'Siti');
        $this->addItem($staff, $unpaidOrder, $variant, 4);

        $this->actingAs($owner)
            ->get(route('owner.orders.index'))
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('Siti')
            ->assertSee($paidOrder->order_code)
            ->assertViewHas('todaySales', 10000.0)
            ->assertViewHas('todayCount', 1);

        $this->actingAs($owner)
            ->get(route('owner.orders.show', $paidOrder))
            ->assertOk()
            ->assertSee('#001')
            ->assertSee('Budi')
            ->assertSee('Riwayat Pembayaran')
            ->assertSee('Tunai')
            ->assertSee('Owner hanya dapat melihat pesanan dan pembayaran.');

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertViewHas('todaySales', 10000.0)
            ->assertViewHas('todayCount', 1);

        $this->actingAs($owner)
            ->post(route('staff.orders.items.store', $paidOrder), [
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('owner.orders.index'))
            ->assertForbidden();
    }

    public function test_staff_without_a_branch_cannot_create_orders_or_restock(): void
    {
        $staff = User::factory()->create(['branch_id' => null]);
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)
            ->post(route('staff.pos.store'), [
                'customer_name' => 'Budi',
                'order_type' => Order::ORDER_TYPE_TAKEAWAY,
            ])
            ->assertSessionHasErrors('branch');

        $this->actingAs($staff)
            ->post(route('staff.stock.store'), [
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('stock');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, BranchStock::firstOrFail()->quantity);
    }

    public function test_staff_dashboard_shows_active_orders_and_recent_payments(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $order = $this->createOrder($staff, 'Budi');
        $this->addItem($staff, $order, $variant, 1);

        $this->actingAs($staff)->post(route('staff.orders.pay', $order), [
            'payment_method' => 'qris',
            'amount' => 10000,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Pesanan Aktif')
            ->assertSee('Budi')
            ->assertViewHas('todaySales', 10000.0)
            ->assertViewHas('activeCount', 1);
    }

    /**
     * @return array{0: Branch, 1: User}
     */
    private function makeStaff(string $branchName = 'Pusat'): array
    {
        $branch = Branch::create(['name' => $branchName, 'is_active' => true]);
        $staff = User::factory()->create(['branch_id' => $branch->id]);

        return [$branch, $staff];
    }

    /**
     * @return array{0: Menu, 1: MenuVariant}
     */
    private function makeMenu(
        Branch $branch,
        string $name,
        string $category,
        float $price,
        int $stock = 10,
        ?string $variantName = null,
    ): array {
        $menu = Menu::create([
            'name' => $name,
            'category' => $category,
            'is_active' => true,
        ]);

        $variant = $menu->variants()->create([
            'variant_name' => $variantName ?? 'Utuh',
            'selling_price' => $price,
            'is_active' => true,
        ]);

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => $stock,
        ]);

        return [$menu, $variant];
    }

    private function createOrder(User $staff, string $customer = 'Budi', string $type = Order::ORDER_TYPE_DINE_IN): Order
    {
        $this->actingAs($staff)
            ->post(route('staff.pos.store'), [
                'customer_name' => $customer,
                'order_type' => $type,
            ])
            ->assertRedirect();

        return Order::query()->latest('id')->firstOrFail();
    }

    private function addItem(User $staff, Order $order, MenuVariant $variant, int $quantity = 1, bool $withTopping = false): void
    {
        $this->actingAs($staff)
            ->post(route('staff.orders.items.store', $order), [
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'with_topping' => $withTopping ? '1' : '0',
            ])
            ->assertRedirect(route('staff.pos.show', $order));
    }

    private function stock(Branch $branch, MenuVariant $variant): int
    {
        return (int) BranchStock::query()
            ->where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity');
    }
}
