<?php

namespace Tests\Feature;

use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\MenuVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_builds_the_expected_food_menu(): void
    {
        $this->seed();

        $foodNames = Menu::where('category', Menu::CATEGORY_MAKANAN)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $this->assertSame(['Jagung Bakar', 'Pisang Bakar', 'Roti Bakar'], $foodNames);
        $this->assertDatabaseMissing('menus', ['name' => 'Pisang Goreng']);
    }

    public function test_jagung_bakar_has_every_flavour_in_both_serving_styles(): void
    {
        $this->seed();

        $jagung = Menu::where('name', 'Jagung Bakar')->firstOrFail();

        $flavours = [
            'Original',
            'Manis',
            'Asin',
            'BBQ',
            'Coklat',
            'Pedas',
            'Pedas Manis',
            'Pedas Asin',
            'Pedas Manis Asin Coklat',
            'Sambal Bangkok',
            'Gula Jawa Pedas Manis',
            'Jasuke',
            'Coklat Keju Susu',
        ];

        $this->assertSame(26, $jagung->variants()->count());

        foreach ($flavours as $flavour) {
            foreach (['Utuh', 'Serut'] as $serving) {
                $this->assertDatabaseHas('menu_variants', [
                    'menu_id' => $jagung->id,
                    'type' => $flavour,
                    'variant_name' => $serving,
                    'is_active' => 1,
                ]);
            }
        }

        $this->assertSame(13, $jagung->variants()->distinct()->count('type'));
    }

    public function test_pisang_bakar_has_every_flavour_for_both_banana_types(): void
    {
        $this->seed();

        $pisang = Menu::where('name', 'Pisang Bakar')->firstOrFail();

        $flavours = [
            'Original',
            'Coklat',
            'Madu',
            'Coklat Susu',
            'Keju Susu',
            'Coklat Keju Susu',
        ];

        $this->assertSame(12, $pisang->variants()->count());
        $this->assertSame(2, $pisang->variants()->distinct()->count('type'));

        foreach (['Pisang Raja', 'Pisang Kepok'] as $type) {
            foreach ($flavours as $flavour) {
                $this->assertDatabaseHas('menu_variants', [
                    'menu_id' => $pisang->id,
                    'type' => $type,
                    'variant_name' => $flavour,
                    'is_active' => 1,
                ]);
            }
        }
    }

    public function test_roti_bakar_has_every_flavour(): void
    {
        $this->seed();

        $roti = Menu::where('name', 'Roti Bakar')->firstOrFail();

        $flavours = [
            'Coklat',
            'Nanas',
            'Strawberry',
            'Blueberry',
            'Kacang',
            'Coklat Susu',
            'Keju Susu',
            'Coklat Keju Susu',
            'Telur',
        ];

        $this->assertSame(count($flavours), $roti->variants()->count());

        foreach ($flavours as $flavour) {
            $this->assertDatabaseHas('menu_variants', [
                'menu_id' => $roti->id,
                'type' => null,
                'variant_name' => $flavour,
                'is_active' => 1,
            ]);
        }
    }

    public function test_seeder_builds_every_hotmad_item_with_every_flavour(): void
    {
        $this->seed();

        $hotmadNames = Menu::where('category', Menu::CATEGORY_CEMILAN)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $this->assertSame(
            ['Bakso', 'Chicken', 'Cireng', 'Jamur', 'Kentang', 'Kulit', 'Nugget', 'Sosis', 'Tahu'],
            $hotmadNames,
        );

        $this->assertDatabaseMissing('menus', ['name' => 'Keripik Singkong']);

        $flavours = [
            'Original',
            'BBQ',
            'Balado',
            'Pedas Level 1',
            'Pedas Level 2',
            'Pedas Level 3',
            'Pedas Level 4',
            'Pedas Level 5',
        ];

        foreach (Menu::where('category', Menu::CATEGORY_CEMILAN)->get() as $menu) {
            $this->assertSame(count($flavours), $menu->variants()->count(), $menu->name);

            foreach ($flavours as $flavour) {
                $this->assertDatabaseHas('menu_variants', [
                    'menu_id' => $menu->id,
                    'type' => null,
                    'variant_name' => $flavour,
                    'is_active' => 1,
                ]);
            }
        }
    }

    public function test_all_food_menus_offer_topping_at_configured_price(): void
    {
        $this->seed();

        $foodMenus = Menu::where('category', Menu::CATEGORY_MAKANAN)->get();

        $this->assertCount(3, $foodMenus);

        foreach ($foodMenus as $menu) {
            $this->assertTrue($menu->hasToppingOption(), $menu->name.' harus punya opsi topping');
            $this->assertSame(
                (int) config('menu.topping_price'),
                $menu->toppingPrice(),
                $menu->name.' memakai harga topping yang salah',
            );
        }
    }

    public function test_every_seeded_variant_has_a_branch_stock(): void
    {
        $this->seed();

        $this->assertSame(
            MenuVariant::count(),
            BranchStock::count(),
        );

        $this->assertSame(0, BranchStock::whereNotIn(
            'menu_variant_id',
            MenuVariant::pluck('id'),
        )->count());
    }
}
