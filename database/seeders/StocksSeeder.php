<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function Laravel\Octane\info;

class StocksSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::table('menus')->exists()) {
            $this->command->error('❌ Table "menus" not found. Aborting.');
            return;
        }

        if (! DB::table('menu_variants')->exists()) {
            $this->command->error('❌ Table "menu_variants" not found. Aborting.');
            return;
        }

        $menus = DB::table('menus')->pluck('id')->toArray();
        $variants = DB::table('menu_variants')->pluck('id')->toArray();

        $total = count($menus) + count($variants);
        $this->command->info("📦 Initializing {$total} stock entries...");

        DB::transaction(function () use ($menus, $variants) {
            foreach ($menus as $id) {
                DB::table('stocks')->insertOrIgnore([
                    'stockable_type' => 'App\\Models\\Menu',
                    'stockable_id' => $id,
                    'quantity' => 999.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($variants as $id) {
                DB::table('stocks')->insertOrIgnore([
                    'stockable_type' => 'App\\Models\\MenuVariant',
                    'stockable_id' => $id,
                    'quantity' => 999.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->command->info("✅ Initialized " . count($menus) . " menu + " . count($variants) . " variant stocks.");
    }
}
