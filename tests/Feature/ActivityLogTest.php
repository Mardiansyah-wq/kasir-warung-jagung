<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_menu_writes_an_activity_log(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('owner.menus.store'), [
            'name' => 'Jagung Bakar',
            'category' => 'makanan',
            'description' => 'Jagung bakar manis.',
            'is_active' => '1',
            'variant_name' => 'Serut',
            'selling_price' => 10000,
        ])->assertRedirect();

        $menu = Menu::where('name', 'Jagung Bakar')->firstOrFail();
        $log = ActivityLog::where('action', ActivityLog::ACTION_MENU_CREATED)->firstOrFail();

        $this->assertSame($owner->id, $log->user_id);
        $this->assertSame($menu->id, $log->subject_id);
        $this->assertSame(Menu::class, $log->subject_type);
        $this->assertSame('Jagung Bakar', $log->subject_label);
        $this->assertSame('Jagung Bakar', $log->changes['name']['new']);
        $this->assertSame('makanan', $log->changes['category']['new']);
        $this->assertTrue($log->changes['is_active']['new']);
        $this->assertStringContainsString('Serut', $log->description);
    }

    public function test_updating_a_menu_records_the_old_and_new_values(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)->put(route('owner.menus.update', $menu), [
            'name' => 'Jagung Bakar Spesial',
            'category' => 'makanan',
            'description' => 'Versi baru.',
            'is_active' => '1',
        ])->assertRedirect();

        $log = ActivityLog::where('action', ActivityLog::ACTION_MENU_UPDATED)->firstOrFail();

        $this->assertSame([
            'old' => 'Jagung Bakar',
            'new' => 'Jagung Bakar Spesial',
        ], $log->changes['name']);

        $this->assertSame([
            'old' => 'Jagung bakar manis.',
            'new' => 'Versi baru.',
        ], $log->changes['description']);

        $this->assertSame('Jagung Bakar Spesial', $log->subject_label);

        $lines = $log->changeLines();
        $this->assertContains('Nama: Jagung Bakar → Jagung Bakar Spesial', $lines);
        $this->assertContains('Deskripsi: Jagung bakar manis. → Versi baru.', $lines);
    }

    public function test_updating_a_menu_without_changes_does_not_write_a_log(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)->put(route('owner.menus.update', $menu), [
            'name' => $menu->name,
            'category' => $menu->category,
            'description' => $menu->description,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame(0, ActivityLog::count());
    }

    public function test_toggling_a_menu_logs_activation_and_deactivation(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)->patch(route('owner.menus.toggle', $menu));
        $this->actingAs($owner)->patch(route('owner.menus.toggle', $menu));

        $activation = ActivityLog::where('action', ActivityLog::ACTION_MENU_DEACTIVATED)->firstOrFail();
        $this->assertSame(['old' => true, 'new' => false], $activation->changes['is_active']);

        $reactivation = ActivityLog::where('action', ActivityLog::ACTION_MENU_ACTIVATED)->firstOrFail();
        $this->assertSame(['old' => false, 'new' => true], $reactivation->changes['is_active']);
    }

    public function test_variant_actions_are_logged(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();
        $variant = $menu->variants()->firstOrFail();

        $this->actingAs($owner)->post(route('owner.menus.variants.store', $menu), [
            'variant_name' => 'Utuh',
            'selling_price' => 15000,
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($owner)->put(route('owner.menus.variants.update', [$menu, $variant]), [
            'variant_name' => 'Serut Besar',
            'selling_price' => 12000,
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($owner)->patch(route('owner.menus.variants.toggle', [$menu, $variant]))
            ->assertRedirect();

        $this->assertSame(1, ActivityLog::where('action', ActivityLog::ACTION_VARIANT_CREATED)->count());
        $this->assertSame(1, ActivityLog::where('action', ActivityLog::ACTION_VARIANT_UPDATED)->count());
        $this->assertSame(1, ActivityLog::where('action', ActivityLog::ACTION_VARIANT_DEACTIVATED)->count());

        $created = ActivityLog::where('action', ActivityLog::ACTION_VARIANT_CREATED)->firstOrFail();
        $this->assertSame(MenuVariant::class, $created->subject_type);
        $this->assertStringContainsString('Utuh', $created->subject_label);

        $updated = ActivityLog::where('action', ActivityLog::ACTION_VARIANT_UPDATED)->firstOrFail();
        $this->assertSame([
            'old' => 'Serut',
            'new' => 'Serut Besar',
        ], $updated->changes['variant_name']);
        $this->assertSame(10000.0, (float) $updated->changes['selling_price']['old']);
        $this->assertSame(12000.0, (float) $updated->changes['selling_price']['new']);
    }

    public function test_activity_page_lists_and_filters_logs(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)->patch(route('owner.menus.toggle', $menu));
        $this->actingAs($owner)->post(route('owner.menus.variants.store', $menu), [
            'variant_name' => 'Utuh',
            'selling_price' => 15000,
            'is_active' => '1',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.activity.index'))
            ->assertOk()
            ->assertSee('Log Aktivitas')
            ->assertSee('Nonaktifkan menu')
            ->assertSee('Tambah varian')
            ->assertSee($menu->name);

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['action' => 'variant']))
            ->assertOk()
            ->assertSee('Tambah varian')
            ->assertDontSee('Nonaktifkan menu');
    }

    public function test_activity_page_supports_search_and_user_filter(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)->patch(route('owner.menus.toggle', $menu));

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['search' => $menu->name]))
            ->assertOk()
            ->assertSee('Nonaktifkan menu');

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['user_id' => $owner->id]))
            ->assertOk()
            ->assertSee($owner->name);

        $other = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['user_id' => $other->id]))
            ->assertOk()
            ->assertDontSee('Nonaktifkan menu');
    }

    public function test_invalid_menu_input_does_not_write_a_log(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('owner.menus.store'), [
            'name' => 'Menu Salah',
            'category' => 'lauk',
            'variant_name' => 'Reguler',
            'selling_price' => -1,
        ])->assertSessionHasErrors(['category', 'selling_price']);

        $this->assertSame(0, ActivityLog::count());
    }

    public function test_staff_cannot_access_the_activity_page(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get(route('owner.activity.index'))
            ->assertForbidden();
    }

    private function createMenu(): Menu
    {
        $menu = Menu::create([
            'name' => 'Jagung Bakar',
            'description' => 'Jagung bakar manis.',
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $menu->variants()->create([
            'variant_name' => 'Serut',
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        return $menu;
    }
}
