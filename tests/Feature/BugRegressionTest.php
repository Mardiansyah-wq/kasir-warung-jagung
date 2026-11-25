<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BugRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_date_filter_is_rejected_instead_of_crashing(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($owner)
            ->get(route('owner.orders.index', ['date' => 'bukan-tanggal']))
            ->assertRedirect()
            ->assertSessionHasErrors('date');

        $this->actingAs($staff)
            ->get(route('staff.orders.index', ['date' => 'bukan-tanggal']))
            ->assertRedirect()
            ->assertSessionHasErrors('date');

        $this->actingAs($owner)
            ->get(route('owner.orders.index', ['date' => '2026-11-25']))
            ->assertOk();
    }

    public function test_deactivating_staff_signs_them_out_on_their_next_request(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($staff)
            ->get(route('staff.pos.index'))
            ->assertOk();

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertRedirect(route('owner.staff.index'));

        $this->withSession([
            'auth_user_id' => $staff->id,
            'auth_password_hash' => (string) $staff->getAuthPassword(),
        ])
            ->actingAs($staff->refresh())
            ->get(route('staff.pos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_resetting_staff_password_signs_them_out_on_their_next_request(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($staff)
            ->get(route('staff.pos.index'))
            ->assertOk();

        $oldPasswordHash = (string) $staff->getAuthPassword();

        $this->actingAs($owner)
            ->post(route('owner.staff.reset-password', $staff), ['password' => 'password-baru-123'])
            ->assertRedirect(route('owner.staff.index'));

        $this->withSession([
            'auth_user_id' => $staff->id,
            'auth_password_hash' => $oldPasswordHash,
        ])
            ->actingAs($staff->refresh())
            ->get(route('staff.pos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_owner_cannot_assign_staff_to_an_inactive_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Cabang Tutup', 'is_active' => false]);

        $this->actingAs($owner)
            ->post(route('owner.staff.store'), [
                'name' => 'Staff Cabang Tutup',
                'email' => 'staff-cabang-tutup@warungjagung.test',
                'phone' => '08123456789',
                'address' => 'Jl. Tutup No. 1',
                'password' => 'password123',
                'branch_id' => $branch->id,
            ])
            ->assertSessionHasErrors('branch_id');

        $this->assertDatabaseMissing('users', ['email' => 'staff-cabang-tutup@warungjagung.test']);
    }

    public function test_merging_an_item_uses_the_current_selling_price(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $menu = Menu::create([
            'name' => 'Jagung Bakar',
            'description' => 'Jagung bakar manis.',
            'category' => Menu::CATEGORY_MAKANAN,
            'is_active' => true,
        ]);

        $variant = $menu->variants()->create([
            'variant_name' => 'Serut',
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 50,
        ]);

        $orders = app(OrderService::class);
        $order = $orders->createOrder($staff, [
            'customer_name' => 'Budi',
            'order_type' => Order::ORDER_TYPE_DINE_IN,
        ]);

        $orders->addItem($staff, $order, $variant->id, 1, false);
        $variant->update(['selling_price' => 12000]);
        $orders->addItem($staff, $order, $variant->id, 1, false);

        $item = $order->items()->firstOrFail();

        $this->assertSame(2, (int) $item->quantity);
        $this->assertSame(12000.0, (float) $item->unit_price);
        $this->assertSame(24000.0, (float) $item->line_total);
    }

    public function test_user_cannot_view_another_users_avatar(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('avatars/owner.jpg', 'owner-avatar');
        Storage::disk('local')->put('avatars/staff.jpg', 'staff-avatar');

        $owner = User::factory()->owner()->create(['photo_path' => 'avatars/owner.jpg']);
        $staff = User::factory()->create(['photo_path' => 'avatars/staff.jpg']);

        $this->actingAs($staff)
            ->get(route('media.users.avatar', $owner))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('media.users.avatar', $staff))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('media.users.avatar', $staff))
            ->assertOk();
    }
}
