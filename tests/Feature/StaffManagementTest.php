<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_see_staff_list_with_pending_registrations(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);

        $active = User::factory()->forBranch($branch)->create(['name' => 'Staff Aktif']);
        $pending = User::factory()->inactive()->create(['name' => 'Staff Pending']);

        $this->actingAs($owner)
            ->get(route('owner.staff.index'))
            ->assertOk()
            ->assertSee('Manajemen Staff')
            ->assertSee('Staff Aktif')
            ->assertSee('Staff Pending')
            ->assertSee('Menunggu')
            ->assertSee($branch->name);
    }

    public function test_owner_can_approve_pending_staff_with_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Cabang Baru', 'is_active' => true]);

        $pending = User::factory()->inactive()->create([
            'name' => 'Calon Staff',
            'branch_id' => null,
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $pending), ['branch_id' => $branch->id])
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success');

        $pending->refresh();

        $this->assertTrue($pending->is_active);
        $this->assertSame($branch->id, $pending->branch_id);
    }

    public function test_approving_pending_staff_requires_a_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $pending = User::factory()->inactive()->create(['branch_id' => null]);

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $pending), [])
            ->assertSessionHasErrors('branch_id');

        $this->assertFalse($pending->refresh()->is_active);
    }

    public function test_owner_can_deactivate_active_staff(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create(['is_active' => true]);

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertSessionHas('success');

        $this->assertFalse($staff->refresh()->is_active);
    }

    public function test_owner_can_create_staff_directly(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('owner.staff.store'), [
                'name' => 'Kasir Baru',
                'email' => 'kasir@example.test',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh 1',
                'password' => 'rahasia123',
                'branch_id' => $branch->id,
            ])
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'kasir@example.test',
            'branch_id' => $branch->id,
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }

    public function test_owner_can_reset_staff_password(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create([
            'email' => 'staff@example.test',
            'password_hash' => 'password-lama',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.staff.reset-password', $staff), [
                'password' => 'password-baru-123',
            ])
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success');

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'staff@example.test',
            'password' => 'password-baru-123',
        ])->assertRedirect(route('staff.dashboard'));
    }

    public function test_staff_cannot_access_staff_management(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($staff)
            ->get(route('owner.staff.index'))
            ->assertForbidden();
    }

    public function test_owner_account_cannot_be_toggled_through_staff_management(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $otherOwner))
            ->assertForbidden();
    }
}
