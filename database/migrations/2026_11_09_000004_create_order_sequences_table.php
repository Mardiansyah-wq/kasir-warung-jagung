<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->date('business_date');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['branch_id', 'business_date'], 'uq_order_sequences_branch_date');
            $table->foreign('branch_id', 'fk_order_sequences_branch')
                ->references('id')
                ->on('branches')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
    }
};
