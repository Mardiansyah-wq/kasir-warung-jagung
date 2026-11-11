<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_see_branch_list(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'address' => 'Jl. Merdeka 1', 'is_active' => true]);

        $this->actingAs($owner)
            ->get(route('owner.branches.index'))
            ->assertOk()
            ->assertSee('Manajemen Cabang')
            ->assertSee($branch->name)
            ->assertSee('Jl. Merdeka 1')
            ->assertSee('Aktif');
    }

    public function test_owner_can_create_a_branch(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.branches.store'), [
                'name' => 'Cabang Kedua',
                'address' => 'Jl. Baru 2',
                'phone' => '081200000000',
            ])
            ->assertRedirect(route('owner.branches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'name' => 'Cabang Kedua',
            'is_active' => true,
        ]);
    }

    public function test_branch_name_must_be_unique(): void
    {
        $owner = User::factory()->owner()->create();
        Branch::create(['name' => 'Pusat', 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('owner.branches.store'), ['name' => 'Pusat'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Branch::where('name', 'Pusat')->count());
    }

    public function test_owner_can_update_a_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);

        $this->actingAs($owner)
            ->put(route('owner.branches.update', $branch), [
                'name' => 'Pusat Baru',
                'address' => 'Jl. Baru 3',
                'phone' => '081299999999',
            ])
            ->assertRedirect(route('owner.branches.index'))
            ->assertSessionHas('success');

        $branch->refresh();

        $this->assertSame('Pusat Baru', $branch->name);
        $this->assertSame('Jl. Baru 3', $branch->address);
    }

    public function test_owner_can_deactivate_an_empty_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Kosong', 'is_active' => true]);

        $this->actingAs($owner)
            ->patch(route('owner.branches.toggle', $branch))
            ->assertRedirect(route('owner.branches.index'))
            ->assertSessionHas('success');

        $this->assertFalse($branch->refresh()->is_active);
    }

    public function test_branch_with_active_staff_cannot_be_deactivated(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        User::factory()->forBranch($branch)->create(['is_active' => true]);

        $this->actingAs($owner)
            ->patch(route('owner.branches.toggle', $branch))
            ->assertRedirect(route('owner.branches.index'))
            ->assertSessionHas('error');

        $this->assertTrue($branch->refresh()->is_active);
    }

    public function test_staff_cannot_access_branch_management(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($staff)
            ->get(route('owner.branches.index'))
            ->assertForbidden();
    }

    public function test_registration_creates_inactive_staff_awaiting_approval(): void
    {
        $this->post(route('register'), [
            'name' => 'Pendaftar Baru',
            'address' => 'Jl. Contoh 5',
            'phone' => '081211112222',
            'email' => 'pendaftar@example.test',
            'password' => 'rahasia123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'pendaftar@example.test',
            'role' => User::ROLE_STAFF,
            'is_active' => false,
            'branch_id' => null,
        ]);
    }

    public function test_pending_staff_gets_clear_login_message(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);

        User::factory()->inactive()->create([
            'email' => 'pending@example.test',
            'password_hash' => 'rahasia123',
            'branch_id' => null,
        ]);

        $this->post(route('login'), [
            'email' => 'pending@example.test',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'menunggu persetujuan',
            session('errors')->first('email'),
        );
    }
}
