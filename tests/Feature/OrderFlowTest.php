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
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_pos_and_see_active_menu(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)
            ->get(route('staff.pos.index'))
            ->assertOk()
            ->assertSee('Kasir')
            ->assertSee('Keranjang')
            ->assertSee($menu->name);
    }

    public function test_pos_page_shows_draft_cart_and_payment_dialog(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
            'with_topping' => '1',
        ]);

        $this->actingAs($staff)
            ->get(route('staff.pos.index'))
            ->assertOk()
            ->assertSee($menu->name)
            ->assertSee('Keranjang')
            ->assertSee('2 item')
            ->assertSee('Bayar Rp 24.000')
            ->assertSee('Uang diterima (Rp)')
            ->assertSee('QRIS');
    }

    public function test_staff_can_open_stock_page_and_see_variants(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)
            ->get(route('staff.stock.index'))
            ->assertOk()
            ->assertSee('Kelola Stok')
            ->assertSee($menu->name)
            ->assertSee($variant->displayName())
            ->assertSee('Stok 10');
    }

    public function test_adding_food_item_creates_draft_with_topping(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)
            ->post(route('staff.orders.items.store'), [
                'variant_id' => $variant->id,
                'quantity' => 2,
                'with_topping' => '1',
            ])
            ->assertRedirect(route('staff.pos.index'));

        $order = Order::firstOrFail();

        $this->assertSame(Order::STATUS_DRAFT, $order->status);
        $this->assertSame($staff->id, $order->created_by);
        $this->assertSame($branch->id, $order->branch_id);
        $this->assertSame('24000.00', $order->subtotal);
        $this->assertSame('24000.00', $order->total_amount);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_variant_id' => $variant->id,
            'unit_price' => 10000,
            'topping_price' => 2000,
            'quantity' => 2,
            'line_total' => 24000,
        ]);
    }

    public function test_topping_is_ignored_for_drinks(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Es Teh', Menu::CATEGORY_MINUMAN, 5000, 10, 'Es Teh');

        $this->actingAs($staff)
            ->post(route('staff.orders.items.store'), [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'with_topping' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('order_items', [
            'menu_variant_id' => $variant->id,
            'topping_price' => 0,
            'line_total' => 5000,
        ]);
    }

    public function test_staff_can_apply_nominal_discount(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount'), [
                'discount_amount' => 4000,
                'reason' => 'Promo pelanggan',
            ])
            ->assertRedirect(route('staff.pos.index'));

        $order = Order::firstOrFail();

        $this->assertSame('20000.00', $order->subtotal);
        $this->assertSame('4000.00', $order->discount_amount);
        $this->assertSame('16000.00', $order->total_amount);

        $this->assertDatabaseHas('order_edit_logs', [
            'order_id' => $order->id,
            'action_type' => 'discount_changed',
            'reason' => 'Promo pelanggan',
        ]);
    }

    public function test_cash_payment_finalizes_order_and_records_movements(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
            'with_topping' => '1',
        ]);

        $response = $this->actingAs($staff)->post(route('staff.orders.finalize'), [
            'payment_method' => 'cash',
            'amount_tendered' => 50000,
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('staff.orders.show', $order));

        $this->assertSame(Order::STATUS_FINALIZED, $order->status);
        $this->assertSame($staff->id, $order->finalized_by);
        $this->assertNotNull($order->finalized_at);

        $this->assertSame(8, BranchStock::where('branch_id', $branch->id)->where('menu_variant_id', $variant->id)->value('quantity'));

        $payment = Payment::firstOrFail();
        $this->assertSame('cash', $payment->payment_method);
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertSame('24000.00', $payment->amount);
        $this->assertSame('50000.00', $payment->amount_tendered);
        $this->assertSame('26000.00', $payment->change_amount);
        $this->assertNotNull($payment->paid_at);

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'user_id' => $staff->id,
            'movement_type' => 'out',
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cash_movements', [
            'branch_id' => $branch->id,
            'user_id' => $staff->id,
            'movement_type' => 'in',
            'category' => CashMovement::CATEGORY_SALE,
            'amount' => 24000,
            'related_payment_id' => $payment->id,
        ]);

        $this->assertDatabaseHas('order_edit_logs', [
            'order_id' => $order->id,
            'action_type' => 'finalized',
        ]);
    }

    public function test_qris_payment_does_not_create_cash_movement(): void
    {
        $this->assertNonCashPayment('qris');
    }

    public function test_transfer_payment_does_not_create_cash_movement(): void
    {
        $this->assertNonCashPayment('transfer');
    }

    public function test_insufficient_stock_blocks_payment_without_changing_data(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 1);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.orders.finalize'), [
                'payment_method' => 'cash',
                'amount_tendered' => 50000,
            ])
            ->assertSessionHasErrors('stock');

        $order = Order::firstOrFail();

        $this->assertSame(Order::STATUS_DRAFT, $order->status);
        $this->assertSame(1, BranchStock::where('branch_id', $branch->id)->where('menu_variant_id', $variant->id)->value('quantity'));
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, StockMovement::count());
        $this->assertSame(0, CashMovement::count());
    }

    public function test_staff_can_restock_then_finish_payment(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 1);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.orders.finalize'), [
                'payment_method' => 'cash',
                'amount_tendered' => 50000,
            ])
            ->assertSessionHasErrors('stock');

        $this->actingAs($staff)
            ->post(route('staff.stock.store'), [
                'variant_id' => $variant->id,
                'quantity' => 5,
                'note' => 'Belanja sendiri',
            ])
            ->assertRedirect();

        $this->assertSame(6, BranchStock::where('branch_id', $branch->id)->where('menu_variant_id', $variant->id)->value('quantity'));

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'user_id' => $staff->id,
            'movement_type' => 'in',
            'quantity' => 5,
            'note' => 'Belanja sendiri',
        ]);

        $this->actingAs($staff)->post(route('staff.orders.finalize'), [
            'payment_method' => 'cash',
            'amount_tendered' => 50000,
        ])->assertRedirect();

        $this->assertSame(Order::STATUS_FINALIZED, Order::firstOrFail()->status);
        $this->assertSame(4, BranchStock::where('branch_id', $branch->id)->where('menu_variant_id', $variant->id)->value('quantity'));
    }

    public function test_cash_tendered_must_cover_total(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.orders.finalize'), [
                'payment_method' => 'cash',
                'amount_tendered' => 5000,
            ])
            ->assertSessionHasErrors('amount_tendered');

        $this->assertSame(Order::STATUS_DRAFT, Order::firstOrFail()->status);
        $this->assertSame(10, BranchStock::where('branch_id', $branch->id)->where('menu_variant_id', $variant->id)->value('quantity'));
        $this->assertSame(0, Payment::count());
    }

    public function test_cancelling_draft_keeps_stock_and_allows_a_new_draft(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $first = Order::firstOrFail();

        $this->actingAs($staff)
            ->post(route('staff.orders.cancel'))
            ->assertRedirect(route('staff.pos.index'));

        $this->assertSame(Order::STATUS_CANCELLED, $first->fresh()->status);
        $this->assertSame(10, BranchStock::where('branch_id', $branch->id)->where('menu_variant_id', $variant->id)->value('quantity'));

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->assertSame(2, Order::count());
        $this->assertNotSame($first->order_code, Order::where('status', Order::STATUS_DRAFT)->value('order_code'));
    }

    public function test_empty_cart_cannot_be_finalized(): void
    {
        [, $staff] = $this->makeStaff();

        $this->actingAs($staff)
            ->post(route('staff.orders.finalize'), [
                'payment_method' => 'cash',
                'amount_tendered' => 10000,
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Payment::count());
    }

    public function test_receipt_page_shows_payment_details(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
            'with_topping' => '1',
        ]);

        $this->actingAs($staff)->post(route('staff.orders.finalize'), [
            'payment_method' => 'cash',
            'amount_tendered' => 20000,
        ]);

        $order = Order::firstOrFail();

        $this->actingAs($staff)
            ->get(route('staff.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee($menu->name)
            ->assertSee('Tunai')
            ->assertSee('Kembalian')
            ->assertSee('Rp 8.000');
    }

    public function test_staff_only_sees_orders_from_their_branch(): void
    {
        [$branchA, $staffA] = $this->makeStaff('Cabang A');
        [$branchB, $staffB] = $this->makeStaff('Cabang B');

        [, $variantA] = $this->makeMenu($branchA, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staffA)->post(route('staff.orders.items.store'), [
            'variant_id' => $variantA->id,
            'quantity' => 1,
        ]);
        $this->actingAs($staffA)->post(route('staff.orders.finalize'), [
            'payment_method' => 'qris',
        ]);

        $order = Order::firstOrFail();

        $this->actingAs($staffB)
            ->get(route('staff.orders.show', $order))
            ->assertForbidden();

        $this->actingAs($staffB)
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertDontSee($order->order_code);
    }

    public function test_draft_is_only_visible_to_its_creator(): void
    {
        [$branch, $staff] = $this->makeStaff();
        $otherStaff = User::factory()->create(['branch_id' => $branch->id]);

        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $order = Order::firstOrFail();

        $this->actingAs($otherStaff)
            ->get(route('staff.orders.show', $order))
            ->assertForbidden();

        $this->actingAs($otherStaff)
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertDontSee($order->order_code);

        $this->actingAs($staff)
            ->get(route('staff.orders.index'))
            ->assertOk()
            ->assertSee($order->order_code);
    }

    public function test_owner_can_view_all_orders_but_cannot_create_transactions(): void
    {
        [$branch, $staff] = $this->makeStaff();
        $owner = User::factory()->owner()->create();
        [$menu, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $this->actingAs($staff)->post(route('staff.orders.finalize'), [
            'payment_method' => 'cash',
            'amount_tendered' => 20000,
        ]);

        $order = Order::firstOrFail();

        $this->actingAs($owner)
            ->get(route('owner.orders.index'))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee($menu->name);

        $this->actingAs($owner)
            ->get(route('owner.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Owner hanya dapat melihat transaksi.');

        $this->actingAs($owner)
            ->post(route('staff.orders.items.store'), [
                'variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('owner.orders.index'))
            ->assertForbidden();
    }

    public function test_staff_without_branch_cannot_transact_or_restock(): void
    {
        $staff = User::factory()->create(['branch_id' => null]);
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)
            ->post(route('staff.orders.items.store'), [
                'variant_id' => $variant->id,
                'quantity' => 1,
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

    public function test_staff_can_update_cart_item_quantity_and_remove_it(): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Jagung Bakar', Menu::CATEGORY_MAKANAN, 10000, 10);

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $item = Order::firstOrFail()->items()->firstOrFail();

        $this->actingAs($staff)
            ->patch(route('staff.orders.items.update', $item), [
                'quantity' => 3,
                'with_topping' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('36000.00', Order::firstOrFail()->total_amount);
        $this->assertSame(3, $item->fresh()->quantity);

        $this->actingAs($staff)
            ->delete(route('staff.orders.items.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
        $this->assertSame('0.00', Order::firstOrFail()->total_amount);
    }

    private function assertNonCashPayment(string $method): void
    {
        [$branch, $staff] = $this->makeStaff();
        [, $variant] = $this->makeMenu($branch, 'Es Teh', Menu::CATEGORY_MINUMAN, 5000, 10, 'Es Teh');

        $this->actingAs($staff)->post(route('staff.orders.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($staff)->post(route('staff.orders.finalize'), [
            'payment_method' => $method,
            'reference_code' => 'REF-123',
        ])->assertRedirect();

        $payment = Payment::firstOrFail();
        $order = Order::firstOrFail();

        $this->assertSame($method, $payment->payment_method);
        $this->assertSame('5000.00', $payment->amount);
        $this->assertSame('5000.00', $payment->amount_tendered);
        $this->assertSame('0.00', $payment->change_amount);
        $this->assertSame('REF-123', $payment->reference_code);
        $this->assertSame(Order::STATUS_FINALIZED, $order->status);
        $this->assertSame(0, CashMovement::count());
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
}
