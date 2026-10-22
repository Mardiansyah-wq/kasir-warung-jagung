<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/owner/dashboard')->assertRedirect('/login');
        $this->get('/staff/dashboard')->assertRedirect('/login');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Hotmad')
            ->assertSee('Email')
            ->assertSee('Daftar sekarang')
            ->assertSee('data-password-toggle="password"', false);
    }

    public function test_owner_is_redirected_to_owner_dashboard(): void
    {
        $user = User::factory()->owner()->create([
            'email' => 'owner@warungjagung.test',
            'password_hash' => 'owner123',
        ]);

        $this->post('/login', [
            'email' => 'owner@warungjagung.test',
            'password' => 'owner123',
        ])->assertRedirect('/owner/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_is_redirected_to_staff_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@warungjagung.test',
            'password_hash' => 'staff123',
        ]);

        $this->post('/login', [
            'email' => 'staff@warungjagung.test',
            'password' => 'staff123',
        ])->assertRedirect('/staff/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'staff@warungjagung.test',
            'password_hash' => 'staff123',
        ]);

        $this->post('/login', [
            'email' => 'staff@warungjagung.test',
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactive()->create([
            'email' => 'staff@warungjagung.test',
            'password_hash' => 'staff123',
        ]);

        $this->post('/login', [
            'email' => 'staff@warungjagung.test',
            'password' => 'staff123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_owner_can_open_owner_dashboard(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get('/owner/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Owner');
    }

    public function test_staff_can_open_staff_dashboard(): void
    {
        $branch = Branch::create(['name' => 'Cabang Pusat']);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($staff)
            ->get('/staff/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Staff')
            ->assertSee('Cabang Pusat');
    }

    public function test_staff_cannot_open_owner_dashboard(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get('/owner/dashboard')
            ->assertForbidden();
    }

    public function test_owner_cannot_open_staff_dashboard(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get('/staff/dashboard')
            ->assertForbidden();
    }

    public function test_dashboard_route_redirects_by_role(): void
    {
        $owner = User::factory()->owner()->create();
        $staff = User::factory()->create();

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertRedirect('/owner/dashboard');

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertRedirect('/staff/dashboard');
    }

    public function test_staff_can_only_access_their_own_branch(): void
    {
        $branch = Branch::create(['name' => 'Cabang Pusat']);
        $otherBranch = Branch::create(['name' => 'Cabang Lain']);

        $staff = User::factory()->forBranch($branch)->create();
        $owner = User::factory()->owner()->create();

        $this->assertTrue($staff->canAccessBranch($branch->id));
        $this->assertFalse($staff->canAccessBranch($otherBranch->id));
        $this->assertTrue($owner->canAccessBranch($otherBranch->id));
    }
}
