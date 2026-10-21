<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 40);
            $table->foreignId('branch_id');
            $table->foreignId('created_by');
            $table->foreignId('finalized_by')->nullable();
            $table->enum('status', ['draft', 'finalized', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('finalized_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->unique('order_code', 'uq_orders_order_code');
            $table->index(['branch_id', 'created_at'], 'idx_orders_branch_created');
            $table->index('status', 'idx_orders_status');
            $table->index('created_by', 'fk_orders_creator');
            $table->index('finalized_by', 'fk_orders_finalizer');
            $table->foreign('branch_id', 'fk_orders_branch')
                ->references('id')
                ->on('branches')
                ->onUpdate('cascade');
            $table->foreign('created_by', 'fk_orders_creator')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
            $table->foreign('finalized_by', 'fk_orders_finalizer')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE `orders` ADD CONSTRAINT `chk_orders_amounts` CHECK (`subtotal` >= 0 and `discount_amount` >= 0 and `total_amount` >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
