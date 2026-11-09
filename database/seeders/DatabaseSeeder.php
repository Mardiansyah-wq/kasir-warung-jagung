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

        $jagungFlavours = [
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

        $jagungVariants = [];

        foreach ($jagungFlavours as $flavour) {
            $jagungVariants[] = ['name' => 'Utuh', 'type' => $flavour, 'price' => 15000, 'stock' => 20];
            $jagungVariants[] = ['name' => 'Serut', 'type' => $flavour, 'price' => 10000, 'stock' => 20];
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
                'description' => 'Pisang bakar manis, cocok untuk camilan hangat.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => [
                    ['name' => 'Normal', 'price' => 10000, 'stock' => 30],
                ],
            ],
            [
                'name' => 'Roti Bakar',
                'description' => 'Roti bakar isi manis, disajikan hangat.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => [
                    ['name' => 'Normal', 'price' => 12000, 'stock' => 30],
                ],
            ],
            [
                'name' => 'Es Teh',
                'description' => 'Es teh manis atau tawar.',
                'category' => Menu::CATEGORY_MINUMAN,
                'variants' => [
                    ['name' => 'Manis', 'price' => 5000, 'stock' => 100],
                    ['name' => 'Tawar', 'price' => 5000, 'stock' => 100],
                ],
            ],
            [
                'name' => 'Keripik Singkong',
                'description' => 'Camilan renyah untuk teman ngobrol.',
                'category' => Menu::CATEGORY_CEMILAN,
                'variants' => [
                    ['name' => 'Original', 'price' => 8000, 'stock' => 50],
                ],
            ],
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
