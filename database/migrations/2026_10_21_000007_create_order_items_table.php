<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('menu_variant_id');
            $table->string('product_name_snapshot', 150);
            $table->string('variant_name_snapshot', 100);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('order_id', 'idx_order_items_order');
            $table->index('menu_variant_id', 'fk_order_items_variant');
            $table->foreign('order_id', 'fk_order_items_order')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade');
            $table->foreign('menu_variant_id', 'fk_order_items_variant')
                ->references('id')
                ->on('menu_variants')
                ->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE `order_items` ADD CONSTRAINT `chk_order_items_values` CHECK (`unit_price` >= 0 and `quantity` > 0 and `line_total` >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
