<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todayOrders = Order::query()
            ->where('status', Order::STATUS_FINALIZED)
            ->whereDate('finalized_at', today());

        return view('owner.dashboard', [
            'branchCount' => Branch::where('is_active', true)->count(),
            'menuCount' => Menu::where('is_active', true)->count(),
            'lowStockCount' => BranchStock::where('quantity', '<', 10)->count(),
            'todayCount' => (clone $todayOrders)->count(),
            'todaySales' => (float) (clone $todayOrders)->sum('total_amount'),
        ]);
    }
}
