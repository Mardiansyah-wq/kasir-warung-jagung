<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_panel_and_route_are_hidden_by_default(): void
    {
        config(['pos.discounts_enabled' => false]);

        [$branch, $staff, $variant] = $this->makeSetup();
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertSee('Catatan pesanan')
            ->assertSee('Simpan Catatan')
            ->assertDontSee('Terapkan diskon')
            ->assertDontSee('data-discount-panel', false);

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), [
                'discount_amount' => 4000,
                'reason' => 'Promo',
            ])
            ->assertNotFound();

        $this->assertSame('0.00', $order->fresh()->discount_amount);
    }

    public function test_discount_panel_and_route_return_when_enabled(): void
    {
        config(['pos.discounts_enabled' => true]);

        [$branch, $staff, $variant] = $this->makeSetup();
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($staff)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertSee('Terapkan diskon')
            ->assertSee('Catatan pesanan');

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), [
                'discount_amount' => 4000,
                'reason' => 'Promo',
            ])
            ->assertRedirect(route('staff.pos.show', $order));

        $this->assertSame('4000.00', $order->fresh()->discount_amount);
    }

    /**
     * @return array{0: Branch, 1: User, 2: MenuVariant}
     */
    private function makeSetup(): array
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
            'order_code' => 'VIS-'.uniqid(),
            'queue_number' => 1,
            'business_date' => today()->toDateString(),
            'customer_name' => 'Budi',
            'order_type' => Order::ORDER_TYPE_DINE_IN,
            'branch_id' => $branch->id,
            'created_by' => $staff->id,
            'status' => Order::STATUS_OPEN,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_variant_id' => $variant->id,
            'product_name_snapshot' => 'Jagung Bakar',
            'variant_name_snapshot' => 'Utuh',
            'topping_price' => 0,
            'unit_price' => 10000,
            'quantity' => 1,
            'is_addon' => false,
            'line_total' => 10000,
        ]);

        return $order;
    }
}
