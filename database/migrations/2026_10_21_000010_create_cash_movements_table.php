<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->foreignId('user_id');
            $table->enum('movement_type', ['in', 'out']);
            $table->enum('category', ['owner_withdrawal', 'operational_expense', 'cash_deposit', 'correction', 'other'])->default('other');
            $table->decimal('amount', 12, 2);
            $table->string('description', 255);
            $table->foreignId('related_payment_id')->nullable();
            $table->dateTime('occurred_at')->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['branch_id', 'occurred_at'], 'idx_cash_movements_branch_time');
            $table->index('category', 'idx_cash_movements_category');
            $table->index('user_id', 'fk_cash_movements_user');
            $table->index('related_payment_id', 'fk_cash_movements_payment');
            $table->foreign('branch_id', 'fk_cash_movements_branch')
                ->references('id')
                ->on('branches')
                ->onUpdate('cascade');
            $table->foreign('related_payment_id', 'fk_cash_movements_payment')
                ->references('id')
                ->on('payments')
                ->onUpdate('cascade');
            $table->foreign('user_id', 'fk_cash_movements_user')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE `cash_movements` ADD CONSTRAINT `chk_cash_movement_amount` CHECK (`amount` > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
