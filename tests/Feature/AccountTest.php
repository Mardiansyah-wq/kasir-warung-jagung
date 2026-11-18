<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_the_account_page(): void
    {
        $owner = User::factory()->owner()->create(['name' => 'Pemilik Warung']);

        $this->actingAs($owner)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee('Akun Saya')
            ->assertSee('Pemilik Warung')
            ->assertSee('Ganti Password');
    }

    public function test_staff_can_open_the_account_page(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee('Akun Saya');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('account.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_update_their_profile(): void
    {
        $user = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($user)
            ->patch(route('account.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@warungjagung.test',
                'phone' => '08123456789',
                'address' => 'Jl. Baru No. 1',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@warungjagung.test', $user->email);
        $this->assertSame('08123456789', $user->phone);
    }

    public function test_user_can_keep_their_own_email(): void
    {
        $user = User::factory()->create(['email' => 'tetap@warungjagung.test']);

        $this->actingAs($user)
            ->patch(route('account.update'), [
                'name' => $user->name,
                'email' => 'tetap@warungjagung.test',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();
    }

    public function test_email_that_belongs_to_another_user_is_rejected(): void
    {
        User::factory()->create(['email' => 'dipakai@warungjagung.test']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.index'))
            ->patch(route('account.update'), [
                'name' => $user->name,
                'email' => 'dipakai@warungjagung.test',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasErrors('email');

        $this->assertNotSame('dipakai@warungjagung.test', $user->fresh()->email);
    }

    public function test_user_can_change_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.password'), [
                'current_password' => 'password',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password_hash));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.index'))
            ->patch(route('account.password'), [
                'current_password' => 'salah',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password_hash));
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.index'))
            ->patch(route('account.password'), [
                'current_password' => 'password',
                'password' => 'password-baru',
                'password_confirmation' => 'beda-lagi',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password_hash));
    }

    public function test_short_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account.index'))
            ->patch(route('account.password'), [
                'current_password' => 'password',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasErrors('password');
    }

    public function test_user_can_upload_profile_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $this->actingAs($user)
            ->patch(route('account.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $file,
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->photo_path);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($user->photo_path);

        $this->actingAs($user)
            ->get(route('media.users.avatar', $user))
            ->assertOk();
    }

    public function test_user_can_remove_profile_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        \Illuminate\Support\Facades\Storage::disk('local')->put('avatars/old.jpg', 'fake-image-content');
        $user = User::factory()->create(['photo_path' => 'avatars/old.jpg']);

        $this->actingAs($user)
            ->patch(route('account.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'remove_photo' => '1',
            ])
            ->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNull($user->photo_path);
        \Illuminate\Support\Facades\Storage::disk('local')->assertMissing('avatars/old.jpg');
    }
}
