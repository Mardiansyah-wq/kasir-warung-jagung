<?php

namespace Database\Seeders;

use App\Models\Branch;
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

        $this->command?->info('Akun owner: owner@warungjagung.test / owner123');
        $this->command?->info('Akun staff: staff@warungjagung.test / staff123');
    }
}
