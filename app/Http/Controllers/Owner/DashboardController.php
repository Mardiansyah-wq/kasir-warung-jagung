<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todayPayments = Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereDate('paid_at', today());

        return view('owner.dashboard', [
            'branchCount' => Branch::where('is_active', true)->count(),
            'menuCount' => Menu::where('is_active', true)->count(),
            'lowStockCount' => BranchStock::where('quantity', '<', 10)->count(),
            'activeCount' => Order::whereIn('status', Order::ACTIVE_STATUSES)->count(),
            'todayCount' => (clone $todayPayments)->count(),
            'todaySales' => (float) (clone $todayPayments)->sum('amount'),
        ]);
    }
}
