<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('action', 40);
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_label', 150);
            $table->string('description', 255);
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('created_at', 'idx_activity_logs_created');
            $table->index(['user_id', 'created_at'], 'idx_activity_logs_user_time');
            $table->index(['subject_type', 'subject_id'], 'idx_activity_logs_subject');
            $table->foreign('user_id', 'fk_activity_logs_user')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
