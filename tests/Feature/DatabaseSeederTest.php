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
