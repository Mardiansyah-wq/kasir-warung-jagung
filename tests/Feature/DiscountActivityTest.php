<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_applying_a_discount_writes_an_activity_log_for_the_owner(): void
    {
        $owner = User::factory()->owner()->create();
        [$branch, $staff, $variant] = $this->makeOrderSetup('Budi');
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), [
                'discount_amount' => 4000,
                'reason' => 'Promo pelanggan',
            ])
            ->assertRedirect(route('staff.pos.show', $order));

        $log = ActivityLog::where('action', ActivityLog::ACTION_ORDER_DISCOUNTED)->firstOrFail();

        $this->assertSame($staff->id, $log->user_id);
        $this->assertSame($branch->id, $log->branch_id);
        $this->assertSame(Order::class, $log->subject_type);
        $this->assertSame($order->id, $log->subject_id);
        $this->assertEquals(['old' => 0, 'new' => 4000], $log->changes['discount_amount']);
        $this->assertSame(['new' => 'Promo pelanggan'], $log->changes['reason']);
        $this->assertStringContainsString('Alasan: Promo pelanggan', $log->description);
        $this->assertStringContainsString('Budi', $log->subject_label);

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['action' => 'order']))
            ->assertOk()
            ->assertSee('Ubah diskon')
            ->assertSee('Promo pelanggan')
            ->assertSee('Diskon: Rp 0 → Rp 4.000')
            ->assertSee($branch->name);

        $this->actingAs($staff)
            ->get(route('staff.activity.index'))
            ->assertOk()
            ->assertDontSee('Ubah diskon');
    }

    public function test_saving_the_same_discount_does_not_add_another_log(): void
    {
        [$branch, $staff, $variant] = $this->makeOrderSetup('Budi');
        $order = $this->makeOrder($branch, $staff, $variant);

        foreach ([1, 2] as $attempt) {
            $this->actingAs($staff)
                ->patch(route('staff.orders.discount', $order), [
                    'discount_amount' => 4000,
                    'reason' => 'Promo pelanggan',
                ])
                ->assertRedirect(route('staff.pos.show', $order));
        }

        $this->assertSame(1, ActivityLog::where('action', ActivityLog::ACTION_ORDER_DISCOUNTED)->count());
    }

    public function test_discount_above_subtotal_is_rejected_without_a_log(): void
    {
        [$branch, $staff, $variant] = $this->makeOrderSetup('Budi');
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), [
                'discount_amount' => 25000,
                'reason' => 'Terlalu besar',
            ])
            ->assertSessionHasErrors('discount_amount');

        $this->assertSame('0.00', $order->fresh()->discount_amount);
        $this->assertSame(0, ActivityLog::where('action', ActivityLog::ACTION_ORDER_DISCOUNTED)->count());
    }

    public function test_removing_a_discount_is_logged_too(): void
    {
        [$branch, $staff, $variant] = $this->makeOrderSetup('Budi');
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)->patch(route('staff.orders.discount', $order), [
            'discount_amount' => 4000,
            'reason' => 'Promo',
        ]);

        $this->actingAs($staff)->patch(route('staff.orders.discount', $order), [
            'discount_amount' => 0,
            'reason' => 'Promo',
        ]);

        $latest = ActivityLog::where('action', ActivityLog::ACTION_ORDER_DISCOUNTED)->latest('id')->firstOrFail();

        $this->assertEquals(['old' => 4000, 'new' => 0], $latest->changes['discount_amount']);
        $this->assertStringContainsString('menjadi Rp 0', $latest->description);
    }

    public function test_changing_only_the_reason_is_logged_without_a_fake_amount_change(): void
    {
        [$branch, $staff, $variant] = $this->makeOrderSetup('Budi');
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)->patch(route('staff.orders.discount', $order), [
            'discount_amount' => 4000,
            'reason' => 'Promo',
        ]);

        $this->actingAs($staff)->patch(route('staff.orders.discount', $order), [
            'discount_amount' => 4000,
            'reason' => 'Teman owner',
        ]);

        $latest = ActivityLog::where('action', ActivityLog::ACTION_ORDER_DISCOUNTED)->latest('id')->firstOrFail();

        $this->assertSame(['old' => 'Promo', 'new' => 'Teman owner'], $latest->changes['reason']);
        $this->assertArrayNotHasKey('discount_amount', $latest->changes);
        $this->assertStringContainsString('tetap Rp 4.000', $latest->description);
        $this->assertStringContainsString('Alasan: Teman owner', $latest->description);
    }

    public function test_pos_page_shows_the_discount_panel_and_last_reason(): void
    {
        [$branch, $staff, $variant] = $this->makeOrderSetup('Budi');
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)->patch(route('staff.orders.discount', $order), [
            'discount_amount' => 4000,
            'reason' => 'Promo Jumat',
        ]);

        $this->actingAs($staff)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertSee('Terapkan diskon')
            ->assertSee('Rp 1.000')
            ->assertSee('Rp 2.000')
            ->assertSee('Rp 5.000')
            ->assertSee('10%')
            ->assertSee('Hapus')
            ->assertSee('Maks Rp 20.000')
            ->assertSee('promo Jumat')
            ->assertSee('Total setelah diskon')
            ->assertSee('Rp 16.000');
    }

    /**
     * @return array{0: Branch, 1: User, 2: MenuVariant}
     */
    private function makeOrderSetup(string $customer): array
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $menu = Menu::create([
            'name' => 'Jagung Bakar',
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $variant = $menu->variants()->create([
            'variant_name' => 'Utuh',
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        return [$branch, $staff, $variant];
    }

    private function makeOrder(Branch $branch, User $staff, MenuVariant $variant): Order
    {
        $order = Order::create([
            'order_code' => 'DIS-'.uniqid(),
            'queue_number' => 1,
            'business_date' => today()->toDateString(),
            'customer_name' => 'Budi',
            'order_type' => Order::ORDER_TYPE_DINE_IN,
            'branch_id' => $branch->id,
            'created_by' => $staff->id,
            'status' => Order::STATUS_OPEN,
            'subtotal' => 20000,
            'discount_amount' => 0,
            'total_amount' => 20000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_variant_id' => $variant->id,
            'product_name_snapshot' => 'Jagung Bakar',
            'variant_name_snapshot' => 'Utuh',
            'topping_price' => 0,
            'unit_price' => 10000,
            'quantity' => 2,
            'is_addon' => false,
            'line_total' => 20000,
        ]);

        return $order;
    }
}
