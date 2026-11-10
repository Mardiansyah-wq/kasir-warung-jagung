<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('queue_number')->nullable()->after('order_code');
            $table->date('business_date')->nullable()->after('queue_number');
            $table->string('customer_name', 120)->default('')->after('business_date');
            $table->enum('order_type', ['dine_in', 'takeaway'])->default('dine_in')->after('customer_name');
            $table->dateTime('paid_at')->nullable()->after('finalized_at');
            $table->dateTime('completed_at')->nullable()->after('paid_at');

            $table->index(['branch_id', 'status'], 'idx_orders_branch_status');
        });

        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft', 'finalized', 'cancelled', 'open', 'processing', 'ready', 'completed') NOT NULL DEFAULT 'open'");
        DB::table('orders')->where('status', 'draft')->update(['status' => 'open']);
        DB::table('orders')->where('status', 'finalized')->update(['status' => 'completed']);
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('open', 'processing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'open'");

        $counters = [];
        $orders = DB::table('orders')->orderBy('id')->get();

        foreach ($orders as $order) {
            $createdAt = $order->created_at ? Carbon::parse($order->created_at) : Carbon::now();
            $businessDate = $createdAt->toDateString();
            $key = $order->branch_id.'|'.$businessDate;

            $counters[$key] = ($counters[$key] ?? 0) + 1;

            $updates = [
                'queue_number' => $counters[$key],
                'business_date' => $businessDate,
                'customer_name' => $order->customer_name ?: 'Pelanggan',
            ];

            if ($order->finalized_at) {
                $updates['paid_at'] = $order->finalized_at;
                $updates['completed_at'] = $order->finalized_at;
            }

            DB::table('orders')->where('id', $order->id)->update($updates);
        }

        foreach ($counters as $key => $lastNumber) {
            [$branchId, $businessDate] = explode('|', $key);

            DB::table('order_sequences')->insertOrIgnore([
                'branch_id' => (int) $branchId,
                'business_date' => $businessDate,
                'last_number' => $lastNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::statement('ALTER TABLE `orders` MODIFY `queue_number` INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `orders` MODIFY `business_date` DATE NOT NULL');

        Schema::table('orders', function (Blueprint $table) {
            $table->unique(['branch_id', 'business_date', 'queue_number'], 'uq_orders_branch_date_queue');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('uq_orders_branch_date_queue');
            $table->dropIndex('idx_orders_branch_status');
            $table->dropColumn(['queue_number', 'business_date', 'customer_name', 'order_type', 'paid_at', 'completed_at']);
        });

        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft', 'finalized', 'cancelled', 'open', 'processing', 'ready', 'completed') NOT NULL DEFAULT 'draft'");
        DB::table('orders')->where('status', 'open')->update(['status' => 'draft']);
        DB::table('orders')->where('status', 'completed')->update(['status' => 'finalized']);
        DB::table('orders')->whereIn('status', ['processing', 'ready'])->update(['status' => 'finalized']);
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('draft', 'finalized', 'cancelled') NOT NULL DEFAULT 'draft'");
    }
};
