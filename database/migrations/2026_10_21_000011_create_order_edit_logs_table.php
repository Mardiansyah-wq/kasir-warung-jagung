<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('user_id');
            $table->enum('action_type', ['created', 'item_added', 'item_updated', 'item_removed', 'discount_changed', 'finalized', 'cancelled', 'other']);
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('reason', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['order_id', 'created_at'], 'idx_order_edit_logs_order_time');
            $table->index('user_id', 'fk_order_edit_logs_user');
            $table->foreign('order_id', 'fk_order_edit_logs_order')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade');
            $table->foreign('user_id', 'fk_order_edit_logs_user')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_edit_logs');
    }
};
