<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id');
            $table->string('variant_name', 100)->default('Reguler');
            $table->string('sku', 60)->nullable();
            $table->decimal('selling_price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['menu_id', 'variant_name'], 'uq_variant_name');
            $table->unique('sku', 'uq_variant_sku');
            $table->foreign('menu_id', 'fk_variants_menu')
                ->references('id')
                ->on('menus')
                ->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE `menu_variants` ADD CONSTRAINT `chk_variant_selling_price` CHECK (`selling_price` >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_variants');
    }
};
