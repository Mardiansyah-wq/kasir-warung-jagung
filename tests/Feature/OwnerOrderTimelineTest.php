<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderEditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerOrderTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_see_who_changed_an_order(): void
    {
        $owner = User::factory()->owner()->create(['name' => 'Pak Owner']);
        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Jagung No. 1',
            'phone' => '081234567890',
            'is_active' => true,
        ]);
        $staff = User::factory()->forBranch($branch)->create(['name' => 'Staf Kasir']);

        $menu = Menu::create([
            'name' => 'Jagung Bakar',
            'description' => 'Jagung bakar spesial',
            'category' => Menu::CATEGORY_MAKANAN,
            'is_active' => true,
        ]);
        $variant = $menu->variants()->create([
            'type' => 'Pedas',
            'variant_name' => 'Utuh',
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_code' => 'TL-'.uniqid(),
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

        OrderEditLog::create([
            'order_id' => $order->id,
            'user_id' => $staff->id,
            'action_type' => 'created',
            'after_data' => [
                'queue_number' => 1,
                'customer_name' => 'Budi',
                'order_type' => Order::ORDER_TYPE_DINE_IN,
            ],
        ]);
        OrderEditLog::create([
            'order_id' => $order->id,
            'user_id' => $staff->id,
            'action_type' => 'item_added',
            'after_data' => [
                'menu_variant_id' => $variant->id,
                'quantity' => 2,
            ],
        ]);
        OrderEditLog::create([
            'order_id' => $order->id,
            'user_id' => $staff->id,
            'action_type' => 'discount_changed',
            'before_data' => ['discount_amount' => 0],
            'after_data' => ['discount_amount' => 2000],
        ]);
        OrderEditLog::create([
            'order_id' => $order->id,
            'user_id' => $staff->id,
            'action_type' => 'paid',
            'after_data' => [
                'payment_method' => 'cash',
                'amount' => 20000,
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('owner.orders.show', $order))
            ->assertOk()
            ->assertSee('Riwayat Perubahan')
            ->assertSee('Pesanan dibuat')
            ->assertSee('Antrian #001 · Budi · Dine-in')
            ->assertSee('Item ditambah')
            ->assertSee('Jagung Bakar · Pedas · Utuh · 2x')
            ->assertSee('Diskon diubah')
            ->assertSee('Diskon Rp 0 → Rp 2.000 · Tanpa alasan')
            ->assertSee('Pembayaran masuk')
            ->assertSee('Tunai Rp 20.000')
            ->assertSee('Oleh Staf Kasir');
    }
}
