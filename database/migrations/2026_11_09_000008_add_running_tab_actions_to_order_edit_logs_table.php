<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `order_edit_logs` MODIFY `action_type` ENUM('created', 'item_added', 'item_updated', 'item_removed', 'discount_changed', 'finalized', 'paid', 'status_changed', 'cancelled', 'other') NOT NULL");
    }

    public function down(): void
    {
        DB::table('order_edit_logs')->whereIn('action_type', ['paid', 'status_changed'])->update(['action_type' => 'other']);

        DB::statement("ALTER TABLE `order_edit_logs` MODIFY `action_type` ENUM('created', 'item_added', 'item_updated', 'item_removed', 'discount_changed', 'finalized', 'cancelled', 'other') NOT NULL");
    }
};
