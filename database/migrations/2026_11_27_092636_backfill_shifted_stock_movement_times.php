<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::table('stock_movements')
            ->whereRaw('TIMESTAMPDIFF(HOUR, occurred_at, created_at) = 7')
            ->update([
                'occurred_at' => DB::raw('DATE_ADD(occurred_at, INTERVAL 7 HOUR)'),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * Corrected rows are indistinguishable from rows that were always right,
     * so shifting them back would corrupt valid history.
     */
    public function down(): void
    {
        //
    }
};
