<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Daftar Akun')
            ->assertSee('Nama Lengkap')
            ->assertSee('Alamat')
            ->assertSee('Nomor Telepon')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('data-password-toggle="password"', false);
    }

    public function test_new_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'address' => 'Jl. Mawar No. 1',
            'phone' => '081234567890',
            'email' => 'budi@gmail.com',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('Jl. Mawar No. 1', $user->address);
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame(User::ROLE_STAFF, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(password_verify('rahasia123', $user->password_hash));
    }

    public function test_registered_name_is_shown_on_dashboard(): void
    {
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'address' => 'Jl. Mawar No. 1',
            'phone' => '081234567890',
            'email' => 'budi@gmail.com',
            'password' => 'rahasia123',
        ])->assertRedirect('/staff/dashboard');

        $this->get('/staff/dashboard')
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'dipakai@gmail.com']);

        $this->post('/register', [
            'name' => 'Budi Santoso',
            'address' => 'Jl. Mawar No. 1',
            'phone' => '081234567890',
            'email' => 'dipakai@gmail.com',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_password_must_be_at_least_eight_characters(): void
    {
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'address' => 'Jl. Mawar No. 1',
            'phone' => '081234567890',
            'email' => 'budi@gmail.com',
            'password' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }
}
