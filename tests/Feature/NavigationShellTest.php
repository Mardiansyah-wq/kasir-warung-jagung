<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_shell_shows_owner_navigation(): void
    {
        $owner = User::factory()->owner()->create(['name' => 'Ardi Pratama']);

        $this->actingAs($owner)
            ->get('/owner/dashboard')
            ->assertOk()
            ->assertSee('class="sidebar"', false)
            ->assertSee('Kelola Akun Pegawai')
            ->assertSee('Riwayat Pesanan')
            ->assertSee('Log Aktivitas')
            ->assertSee('Pusat bantuan')
            ->assertSee('aria-current="page"', false)
            ->assertSee('Ardi Pratama')
            ->assertSee('Owner')
            ->assertSee('Kedai tutup');
    }

    public function test_staff_shell_shows_staff_navigation_only(): void
    {
        $branch = Branch::create(['name' => 'Cabang Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        $this->actingAs($staff)
            ->get('/staff/dashboard')
            ->assertOk()
            ->assertSee('Kasir')
            ->assertSee('Cabang Pusat')
            ->assertSee('Kedai buka')
            ->assertDontSee('Kelola Akun Pegawai')
            ->assertSee('Staff');
    }

    public function test_guest_pages_have_no_shell(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('class="sidebar"', false)
            ->assertDontSee('Kedai buka');
    }
}
