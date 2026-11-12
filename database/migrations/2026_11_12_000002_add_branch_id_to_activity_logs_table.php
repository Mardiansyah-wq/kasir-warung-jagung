<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');
            $table->index('branch_id', 'idx_activity_logs_branch');
            $table->foreign('branch_id', 'fk_activity_logs_branch')
                ->references('id')
                ->on('branches')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign('fk_activity_logs_branch');
            $table->dropIndex('idx_activity_logs_branch');
            $table->dropColumn('branch_id');
        });
    }
};
