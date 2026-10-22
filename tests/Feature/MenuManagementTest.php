<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_menu_list(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)
            ->get(route('owner.menus.index'))
            ->assertOk()
            ->assertSee('Kelola Menu')
            ->assertSee($menu->name)
            ->assertSee('2 varian aktif');
    }

    public function test_owner_can_create_menu_with_first_variant(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post(route('owner.menus.store'), [
            'name' => 'Jagung Bakar',
            'category' => 'makanan',
            'description' => 'Jagung bakar manis.',
            'is_active' => '1',
            'variant_name' => 'Serut',
            'selling_price' => 10000,
        ]);

        $menu = Menu::where('name', 'Jagung Bakar')->firstOrFail();

        $response->assertRedirect(route('owner.menus.edit', $menu));

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Jagung Bakar',
            'category' => 'makanan',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('menu_variants', [
            'menu_id' => $menu->id,
            'variant_name' => 'Serut',
            'selling_price' => 10000,
            'is_active' => 1,
        ]);
    }

    public function test_owner_can_add_update_and_toggle_variants(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)
            ->post(route('owner.menus.variants.store', $menu), [
                'variant_name' => 'Utuh',
                'selling_price' => 15000,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $variant = $menu->variants()->where('variant_name', 'Utuh')->firstOrFail();

        $this->actingAs($owner)
            ->put(route('owner.menus.variants.update', [$menu, $variant]), [
                'variant_name' => 'Utuh Besar',
                'selling_price' => 16000,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('menu_variants', [
            'id' => $variant->id,
            'variant_name' => 'Utuh Besar',
            'selling_price' => 16000,
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.menus.variants.toggle', [$menu, $variant]))
            ->assertRedirect();

        $this->assertFalse($variant->fresh()->is_active);
    }

    public function test_variant_name_must_be_unique_per_menu(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)
            ->post(route('owner.menus.variants.store', $menu), [
                'variant_name' => 'Serut',
                'selling_price' => 12000,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('variant_name');
    }

    public function test_owner_can_toggle_menu(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->createMenu();

        $this->actingAs($owner)
            ->patch(route('owner.menus.toggle', $menu))
            ->assertRedirect();

        $this->assertFalse($menu->fresh()->is_active);

        $this->actingAs($owner)
            ->patch(route('owner.menus.toggle', $menu))
            ->assertRedirect();

        $this->assertTrue($menu->fresh()->is_active);
    }

    public function test_menu_validation_rejects_invalid_data(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Salah',
                'category' => 'lauk',
                'variant_name' => 'Reguler',
                'selling_price' => -1,
            ])
            ->assertSessionHasErrors(['category', 'selling_price']);
    }

    public function test_staff_cannot_manage_menus(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get(route('owner.menus.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Staff',
                'category' => 'makanan',
                'variant_name' => 'Reguler',
                'selling_price' => 10000,
            ])
            ->assertForbidden();
    }

    public function test_staff_sees_variants_and_only_their_branch_stock(): void
    {
        $branch = Branch::create(['name' => 'Cabang A']);
        $otherBranch = Branch::create(['name' => 'Cabang B']);
        $staff = User::factory()->forBranch($branch)->create();

        $menu = $this->createMenu();
        $variant = $menu->variants()->firstOrFail();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 73,
        ]);

        BranchStock::create([
            'branch_id' => $otherBranch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 97,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('staff.menus.index'))
            ->assertOk()
            ->assertSee('Jagung Bakar')
            ->assertSee('Serut')
            ->assertSee('Utuh');

        $response->assertSee('&quot;stock&quot;:73', false);
        $response->assertDontSee('&quot;stock&quot;:97', false);
    }

    public function test_staff_does_not_see_inactive_menu_or_variant(): void
    {
        $staff = User::factory()->create();
        $active = $this->createMenu();

        Menu::create([
            'name' => 'Menu Nonaktif',
            'category' => 'makanan',
            'is_active' => false,
        ]);

        $active->variants()->create([
            'variant_name' => 'Varian Nonaktif',
            'selling_price' => 9000,
            'is_active' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.menus.index'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee('Menu Nonaktif')
            ->assertDontSee('Varian Nonaktif');
    }

    public function test_topping_note_only_appears_for_food(): void
    {
        $staff = User::factory()->create();

        $this->createMenu();

        Menu::create([
            'name' => 'Es Teh',
            'category' => 'minuman',
            'is_active' => true,
        ])->variants()->create([
            'variant_name' => 'Manis',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        $content = $this->actingAs($staff)
            ->get(route('staff.menus.index'))
            ->assertOk()
            ->assertSee('Es Teh')
            ->getContent();

        $this->assertSame(1, substr_count($content, 'Bisa tambah topping +Rp 2.000'));
        $this->assertSame(1, substr_count($content, 'data-menu-topping="2000"'));
        $this->assertSame(1, substr_count($content, 'data-menu-topping="0"'));
    }

    public function test_staff_can_filter_menus_by_category(): void
    {
        $staff = User::factory()->create();

        $this->createMenu();

        Menu::create([
            'name' => 'Es Teh',
            'category' => 'minuman',
            'is_active' => true,
        ])->variants()->create([
            'variant_name' => 'Manis',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.menus.index', ['category' => 'minuman']))
            ->assertOk()
            ->assertSee('Es Teh')
            ->assertDontSee('Jagung Bakar');
    }

    private function createMenu(): Menu
    {
        $menu = Menu::create([
            'name' => 'Jagung Bakar',
            'description' => 'Jagung bakar manis.',
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $menu->variants()->createMany([
            [
                'variant_name' => 'Serut',
                'selling_price' => 10000,
                'is_active' => true,
            ],
            [
                'variant_name' => 'Utuh',
                'selling_price' => 15000,
                'is_active' => true,
            ],
        ]);

        return $menu->load('variants');
    }
}
