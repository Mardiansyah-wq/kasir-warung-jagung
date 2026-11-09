<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $branch = Branch::updateOrCreate(
            ['name' => 'Cabang Pusat'],
            [
                'address' => 'Jl. Contoh No. 1',
                'phone' => '081234567890',
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'branch_id' => null,
                'name' => 'Owner',
                'email' => 'owner@warungjagung.test',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh No. 1',
                'password_hash' => 'owner123',
                'role' => User::ROLE_OWNER,
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['username' => 'staff'],
            [
                'branch_id' => $branch->id,
                'name' => 'Staff',
                'email' => 'staff@warungjagung.test',
                'phone' => '081298765432',
                'address' => 'Jl. Contoh No. 1',
                'password_hash' => 'staff123',
                'role' => User::ROLE_STAFF,
                'is_active' => true,
            ],
        );

        $jagungPrices = [
            'Original' => 8000,
            'Manis' => 8000,
            'Asin' => 8000,
            'BBQ' => 8000,
            'Coklat' => 8000,
            'Pedas' => 8000,
            'Pedas Manis' => 8000,
            'Pedas Asin' => 8000,
            'Pedas Manis Asin' => 8000,
            'Pedas Manis Asin Coklat' => 8000,
            'Sambal Bangkok' => 8000,
            'Gula Jawa Pedas Manis' => 8000,
            'Jasuke' => 10000,
            'Coklat Keju Susu' => 10000,
        ];

        $jagungVariants = [];

        foreach ($jagungPrices as $flavour => $price) {
            $jagungVariants[] = ['name' => 'Utuh', 'type' => $flavour, 'price' => $price, 'stock' => 20];
            $jagungVariants[] = ['name' => 'Serut', 'type' => $flavour, 'price' => $price, 'stock' => 20];
        }

        $pisangFlavours = [
            'Original',
            'Coklat',
            'Madu',
            'Coklat Susu',
            'Keju Susu',
            'Coklat Keju Susu',
        ];

        $pisangVariants = [];

        // Harga placeholder per jenis pisang; nanti diisi lewat halaman menu.
        foreach (['Pisang Raja' => 12000, 'Pisang Kepok' => 10000] as $type => $price) {
            foreach ($pisangFlavours as $flavour) {
                $pisangVariants[] = ['name' => $flavour, 'type' => $type, 'price' => $price, 'stock' => 20];
            }
        }

        $rotiFlavours = [
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

        // Harga placeholder; nanti diisi lewat halaman menu.
        $rotiVariants = array_map(
            fn (string $flavour): array => ['name' => $flavour, 'price' => 12000, 'stock' => 20],
            $rotiFlavours,
        );

        $hotmadFlavours = [
            'Original',
            'BBQ',
            'Balado',
            'Pedas Level 1',
            'Pedas Level 2',
            'Pedas Level 3',
            'Pedas Level 4',
            'Pedas Level 5',
        ];

        $hotmadItems = [
            'Chicken',
            'Sosis',
            'Bakso',
            'Nugget',
            'Kentang',
            'Jamur',
            'Kulit',
            'Cireng',
            'Tahu',
        ];

        // Harga placeholder; nanti diisi lewat halaman menu.
        $hotmadMenus = array_map(fn (string $item): array => [
            'name' => $item,
            'description' => 'Hotmad '.$item.' dengan pilihan rasa original, BBQ, balado, dan level pedas.',
            'category' => Menu::CATEGORY_CEMILAN,
            'variants' => array_map(
                fn (string $flavour): array => ['name' => $flavour, 'price' => 10000, 'stock' => 20],
                $hotmadFlavours,
            ),
        ], $hotmadItems);

        $esPanasDrinks = [
            'Milo',
            'Chocolatos',
            'Cappucino',
            'Jeruk Peras',
            'Nutrisari',
            'Teh Tarik',
            'Teh',
            'Lemon Tea',
        ];

        // Harga placeholder; nanti diisi lewat halaman menu.
        $singleDrinks = [
            'Leci Squash' => 10000,
            'Lemon Squash' => 10000,
            'Moka Squash' => 10000,
            'Soda Gembira' => 10000,
            'Kopi' => 8000,
            'Kopi Susu' => 10000,
            'Jahe Geprek' => 8000,
            'Jahe Susu' => 10000,
            'Jahe Sere Aren' => 10000,
            'Air Mineral' => 5000,
        ];

        $minumanMenus = [];

        foreach ($esPanasDrinks as $drink) {
            $minumanMenus[] = [
                'name' => $drink,
                'description' => $drink.' tersedia dalam pilihan es atau panas.',
                'category' => Menu::CATEGORY_MINUMAN,
                'variants' => [
                    ['name' => 'Es', 'price' => 8000, 'stock' => 50],
                    ['name' => 'Panas', 'price' => 8000, 'stock' => 50],
                ],
            ];
        }

        foreach ($singleDrinks as $drink => $price) {
            $minumanMenus[] = [
                'name' => $drink,
                'description' => 'Minuman '.$drink.'.',
                'category' => Menu::CATEGORY_MINUMAN,
                'variants' => [
                    ['name' => 'Normal', 'price' => $price, 'stock' => 50],
                ],
            ];
        }

        $menus = [
            [
                'name' => 'Jagung Bakar',
                'description' => 'Jagung bakar dengan pilihan rasa, disajikan utuh atau serut.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => $jagungVariants,
            ],
            [
                'name' => 'Pisang Bakar',
                'description' => 'Pisang bakar dengan pilihan jenis pisang dan rasa.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => $pisangVariants,
            ],
            [
                'name' => 'Roti Bakar',
                'description' => 'Roti bakar dengan pilihan rasa manis dan gurih.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => $rotiVariants,
            ],
            ...$minumanMenus,
            ...$hotmadMenus,
        ];

        foreach ($menus as $data) {
            $menu = Menu::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'is_active' => true,
                ],
            );

            foreach ($data['variants'] as $v) {
                $variant = MenuVariant::updateOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'type' => $v['type'] ?? null,
                        'variant_name' => $v['name'],
                    ],
                    [
                        'selling_price' => $v['price'],
                        'is_active' => true,
                    ],
                );

                BranchStock::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'menu_variant_id' => $variant->id,
                    ],
                    [
                        'quantity' => $v['stock'],
                    ],
                );
            }
        }

        $this->command?->info('Akun owner: owner@warungjagung.test / owner123');
        $this->command?->info('Akun staff: staff@warungjagung.test / staff123');
        $this->command?->info('Menu contoh Jagung Bakar WJT berhasil disiapkan.');
    }
}
