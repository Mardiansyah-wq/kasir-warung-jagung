<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable();
            $table->string('name', 120);
            $table->string('username', 60);
            $table->string('password_hash');
            $table->enum('role', ['owner', 'staff'])->default('staff');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique('username', 'uq_users_username');
            $table->index('branch_id', 'idx_users_branch');
            $table->foreign('branch_id', 'fk_users_branch')
                ->references('id')
                ->on('branches')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
