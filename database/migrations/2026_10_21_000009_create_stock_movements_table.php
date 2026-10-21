<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->foreignId('menu_variant_id');
            $table->foreignId('user_id');
            $table->enum('movement_type', ['in', 'out', 'adjustment']);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('note', 255)->nullable();
            $table->dateTime('occurred_at')->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['branch_id', 'occurred_at'], 'idx_stock_movements_branch_time');
            $table->index(['menu_variant_id', 'occurred_at'], 'idx_stock_movements_variant_time');
            $table->index('user_id', 'fk_stock_movements_user');
            $table->foreign('branch_id', 'fk_stock_movements_branch')
                ->references('id')
                ->on('branches')
                ->onUpdate('cascade');
            $table->foreign('menu_variant_id', 'fk_stock_movements_variant')
                ->references('id')
                ->on('menu_variants')
                ->onUpdate('cascade');
            $table->foreign('user_id', 'fk_stock_movements_user')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE `stock_movements` ADD CONSTRAINT `chk_stock_movement_quantity` CHECK (`quantity` > 0)');
        DB::statement('ALTER TABLE `stock_movements` ADD CONSTRAINT `chk_stock_unit_cost` CHECK (`unit_cost` is null or `unit_cost` >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
