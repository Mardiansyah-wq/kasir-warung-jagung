<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 60)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNull('username')
            ->update(['username' => DB::raw('CONCAT("user_", id)')]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 60)->nullable(false)->change();
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'phone', 'address']);
        });
    }
};
