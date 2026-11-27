<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            ->assertSee($branch->name)
            ->assertSee('Setujui &amp; aktifkan', false)
            ->assertSee('Nonaktifkan akses')
            ->assertDontSee('Reset password');
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

    public function test_owner_can_reactivate_inactive_staff_who_already_has_a_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Cabang 2', 'is_active' => true]);

        $staff = User::factory()->forBranch($branch)->inactive()->create([
            'name' => 'Ariel',
            'email' => 'ariel@example.test',
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        $staff->refresh();

        $this->assertTrue($staff->is_active);
        $this->assertSame($branch->id, $staff->branch_id);
    }

    public function test_reactivating_staff_on_an_inactive_branch_requires_a_new_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $oldBranch = Branch::create(['name' => 'Cabang Lama', 'is_active' => false]);
        $newBranch = Branch::create(['name' => 'Cabang Baru', 'is_active' => true]);

        $staff = User::factory()->forBranch($oldBranch)->inactive()->create();

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertSessionHasErrors('branch_id');

        $this->assertFalse($staff->refresh()->is_active);

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff), ['branch_id' => $newBranch->id])
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success');

        $staff->refresh();

        $this->assertTrue($staff->is_active);
        $this->assertSame($newBranch->id, $staff->branch_id);
    }

    public function test_activated_staff_can_log_in_with_the_existing_password(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Cabang 2', 'is_active' => true]);

        $staff = User::factory()->forBranch($branch)->inactive()->create([
            'email' => 'ariel@example.test',
            'password_hash' => 'rahasia123',
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success');

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'ariel@example.test',
            'password' => 'rahasia123',
        ])->assertRedirect(route('staff.dashboard'));

        $this->assertAuthenticatedAs($staff->refresh());
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

    public function test_staff_lifecycle_is_managed_entirely_from_the_ui(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Cabang 2', 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('owner.staff.store'), [
                'name' => 'Kasir Baru',
                'email' => 'kasir-baru@example.test',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh 1',
                'password' => 'rahasia123',
                'branch_id' => $branch->id,
            ])
            ->assertRedirect(route('owner.staff.index'))
            ->assertSessionHas('success');

        $staff = User::where('email', 'kasir-baru@example.test')->firstOrFail();

        $this->assertTrue($staff->is_active);
        $this->assertSame($branch->id, $staff->branch_id);
        $this->assertNotSame('rahasia123', $staff->password_hash);
        $this->assertTrue(Hash::check('rahasia123', $staff->password_hash));

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'kasir-baru@example.test',
            'password' => 'rahasia123',
        ])->assertRedirect(route('staff.dashboard'));

        $this->assertAuthenticatedAs($staff);

        $this->post(route('logout'));

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertSessionHas('success');

        $this->assertFalse($staff->refresh()->is_active);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'kasir-baru@example.test',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($owner)
            ->patch(route('owner.staff.toggle', $staff))
            ->assertSessionHas('success');

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'kasir-baru@example.test',
            'password' => 'rahasia123',
        ])->assertRedirect(route('staff.dashboard'));

        $this->assertAuthenticatedAs($staff->refresh());
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
