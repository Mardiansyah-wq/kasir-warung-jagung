<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `cash_movements` MODIFY `category` ENUM('owner_withdrawal', 'operational_expense', 'cash_deposit', 'correction', 'other', 'sale') NOT NULL DEFAULT 'other'");
    }

    public function down(): void
    {
        DB::table('cash_movements')->where('category', 'sale')->update(['category' => 'other']);

        DB::statement("ALTER TABLE `cash_movements` MODIFY `category` ENUM('owner_withdrawal', 'operational_expense', 'cash_deposit', 'correction', 'other') NOT NULL DEFAULT 'other'");
    }
};
