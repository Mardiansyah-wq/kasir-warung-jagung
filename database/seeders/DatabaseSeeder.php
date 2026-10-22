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

        $menus = [
            [
                'name' => 'Jagung Bakar',
                'description' => 'Jagung bakar manis, tersedia serut dan utuh.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => [
                    'Serut' => ['price' => 10000, 'stock' => 40],
                    'Utuh' => ['price' => 15000, 'stock' => 30],
                ],
            ],
            [
                'name' => 'Pisang Goreng',
                'description' => 'Pisang goreng renyah dengan pilihan pisang raja atau kepok.',
                'category' => Menu::CATEGORY_MAKANAN,
                'variants' => [
                    'Pisang Raja' => ['price' => 12000, 'stock' => 25],
                    'Pisang Kepok' => ['price' => 10000, 'stock' => 35],
                ],
            ],
            [
                'name' => 'Es Teh',
                'description' => 'Es teh manis atau tawar.',
                'category' => Menu::CATEGORY_MINUMAN,
                'variants' => [
                    'Manis' => ['price' => 5000, 'stock' => 100],
                    'Tawar' => ['price' => 5000, 'stock' => 100],
                ],
            ],
            [
                'name' => 'Keripik Singkong',
                'description' => 'Cemilan renyah untuk teman ngobrol.',
                'category' => Menu::CATEGORY_CEMILAN,
                'variants' => [
                    'Original' => ['price' => 8000, 'stock' => 50],
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

            foreach ($data['variants'] as $variantName => $variantData) {
                $variant = MenuVariant::updateOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'variant_name' => $variantName,
                    ],
                    [
                        'selling_price' => $variantData['price'],
                        'is_active' => true,
                    ],
                );

                BranchStock::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'menu_variant_id' => $variant->id,
                    ],
                    [
                        'quantity' => $variantData['stock'],
                    ],
                );
            }
        }

        $this->command?->info('Akun owner: owner@warungjagung.test / owner123');
        $this->command?->info('Akun staff: staff@warungjagung.test / staff123');
        $this->command?->info('Menu contoh Hotmad berhasil disiapkan.');
    }
}
