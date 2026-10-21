<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->foreignId('menu_variant_id');
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['branch_id', 'menu_variant_id'], 'uq_branch_variant_stock');
            $table->index('menu_variant_id', 'fk_stocks_variant');
            $table->foreign('branch_id', 'fk_stocks_branch')
                ->references('id')
                ->on('branches')
                ->onUpdate('cascade');
            $table->foreign('menu_variant_id', 'fk_stocks_variant')
                ->references('id')
                ->on('menu_variants')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};
