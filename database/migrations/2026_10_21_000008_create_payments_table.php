<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'other']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('reference_code', 100)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index(['order_id', 'status'], 'idx_payments_order_status');
            $table->index('paid_at', 'idx_payments_paid_at');
            $table->foreign('order_id', 'fk_payments_order')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE `payments` ADD CONSTRAINT `chk_payments_amount` CHECK (`amount` > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
