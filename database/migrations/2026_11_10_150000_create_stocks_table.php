<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stockable_type');
            $table->unsignedBigInteger('stockable_id');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['stockable_type', 'stockable_id'], 'uq_stocks_stockable');
            $table->index(['stockable_type', 'stockable_id'], 'idx_stocks_stockable');
            $table->engine = 'InnoDB';
        });

        // Add check constraint for non-negative quantity (MySQL 8.0.16+)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `stocks` ADD CONSTRAINT `chk_stocks_quantity_non_negative` CHECK (`quantity` >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};