<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:owner'])
            ->get('/_test/owner-only', fn () => 'ok');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Warung Jagung Bakar')
            ->assertSee('Username');
    }

    public function test_owner_can_login_with_username_and_password(): void
    {
        $user = User::factory()->owner()->create([
            'username' => 'owner',
            'password_hash' => 'owner123',
        ]);

        $response = $this->post('/login', [
            'username' => 'owner',
            'password' => 'owner123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'username' => 'staff',
            'password_hash' => 'staff123',
        ]);

        $this->post('/login', [
            'username' => 'staff',
            'password' => 'salah',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactive()->create([
            'username' => 'staff',
            'password_hash' => 'staff123',
        ]);

        $this->post('/login', [
            'username' => 'staff',
            'password' => 'staff123',
        ])->assertSessionHasErrors('username');

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

    public function test_staff_cannot_access_owner_only_route(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get('/_test/owner-only')
            ->assertForbidden();
    }

    public function test_owner_can_access_owner_only_route(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get('/_test/owner-only')
            ->assertOk();
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
