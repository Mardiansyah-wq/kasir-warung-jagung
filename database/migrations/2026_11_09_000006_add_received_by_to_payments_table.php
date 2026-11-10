<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('received_by')->nullable()->after('order_id');
            $table->foreign('received_by', 'fk_payments_received_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });

        DB::statement('UPDATE `payments` p JOIN `orders` o ON o.id = p.order_id SET p.received_by = o.finalized_by WHERE p.received_by IS NULL AND o.finalized_by IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('fk_payments_received_by');
            $table->dropColumn('received_by');
        });
    }
};
