<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_enable_and_disable_manual_discount(): void
    {
        $owner = User::factory()->owner()->create();
        [$branch, $staff, $variant] = $this->makeSetup();
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->assertFalse(Setting::discountsEnabled());

        $this->actingAs($owner)
            ->get(route('owner.settings.index'))
            ->assertOk()
            ->assertSee('Diskon Manual di Kasir')
            ->assertSee('Nonaktif');

        $this->actingAs($owner)
            ->patch(route('owner.settings.discount'), ['enabled' => 1])
            ->assertRedirect(route('owner.settings.index'));

        $this->assertTrue(Setting::discountsEnabled());

        $this->actingAs($staff)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertSee('Terapkan diskon');

        $this->actingAs($owner)
            ->patch(route('owner.settings.discount'), ['enabled' => 0])
            ->assertRedirect(route('owner.settings.index'));

        $this->assertFalse(Setting::discountsEnabled());

        $this->actingAs($staff)
            ->get(route('staff.pos.show', $order))
            ->assertOk()
            ->assertDontSee('Terapkan diskon')
            ->assertSee('Catatan pesanan');
    }

    public function test_staff_cannot_open_or_change_the_settings(): void
    {
        [$branch, $staff] = $this->makeSetup();

        $this->actingAs($staff)
            ->get(route('owner.settings.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('owner.settings.discount'), ['enabled' => 1])
            ->assertForbidden();

        $this->assertFalse(Setting::discountsEnabled());
    }

    public function test_discount_route_closes_again_after_owner_disables_it(): void
    {
        $owner = User::factory()->owner()->create();
        [$branch, $staff, $variant] = $this->makeSetup();
        $order = $this->makeOrder($branch, $staff, $variant);

        $this->actingAs($owner)
            ->patch(route('owner.settings.discount'), ['enabled' => 1])
            ->assertRedirect(route('owner.settings.index'));

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), ['discount_amount' => 1000])
            ->assertRedirect(route('staff.pos.show', $order));

        $this->actingAs($owner)
            ->patch(route('owner.settings.discount'), ['enabled' => 0])
            ->assertRedirect(route('owner.settings.index'));

        $this->actingAs($staff)
            ->patch(route('staff.orders.discount', $order), ['discount_amount' => 2000])
            ->assertNotFound();

        $this->assertFalse(Setting::discountsEnabled());
        $this->assertEquals(1000.0, (float) $order->fresh()->discount_amount);
    }

    public function test_discount_setting_rejects_invalid_values(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->from(route('owner.settings.index'))
            ->patch(route('owner.settings.discount'), ['enabled' => 'abc'])
            ->assertRedirect(route('owner.settings.index'))
            ->assertSessionHasErrors('enabled');

        $this->actingAs($owner)
            ->from(route('owner.settings.index'))
            ->patch(route('owner.settings.discount'), ['enabled' => 2])
            ->assertRedirect(route('owner.settings.index'))
            ->assertSessionHasErrors('enabled');

        $this->assertFalse(Setting::discountsEnabled());
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
            'order_code' => 'SET-'.uniqid(),
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
