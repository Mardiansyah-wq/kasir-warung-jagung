<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $todayOrders = Order::query()
            ->where('branch_id', $branchId)
            ->where('status', Order::STATUS_FINALIZED)
            ->whereDate('finalized_at', today());

        return view('staff.dashboard', [
            'branch' => $user->branch,
            'menuCount' => Menu::query()
                ->where('is_active', true)
                ->whereHas('variants', fn ($query) => $query->where('is_active', true))
                ->count(),
            'stockCount' => $branchId
                ? (int) BranchStock::where('branch_id', $branchId)->sum('quantity')
                : 0,
            'draftCount' => Order::query()
                ->where('branch_id', $branchId)
                ->where('created_by', $user->id)
                ->where('status', Order::STATUS_DRAFT)
                ->count(),
            'todayCount' => (clone $todayOrders)->count(),
            'todaySales' => (float) (clone $todayOrders)->sum('total_amount'),
        ]);
    }
}
