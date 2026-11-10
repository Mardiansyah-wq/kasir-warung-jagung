<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $todayPayments = Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereDate('paid_at', today())
            ->when(
                $branchId,
                fn ($query) => $query->whereHas('order', fn ($order) => $order->where('branch_id', $branchId)),
                fn ($query) => $query->whereRaw('1 = 0'),
            );

        return view('staff.dashboard', [
            'branch' => $user->branch,
            'menuCount' => Menu::query()
                ->where('is_active', true)
                ->whereHas('variants', fn ($query) => $query->where('is_active', true))
                ->count(),
            'stockCount' => $branchId
                ? (int) BranchStock::where('branch_id', $branchId)->sum('quantity')
                : 0,
            'activeCount' => Order::query()
                ->where('branch_id', $branchId)
                ->whereIn('status', Order::ACTIVE_STATUSES)
                ->count(),
            'openCount' => Order::query()
                ->where('branch_id', $branchId)
                ->where('status', Order::STATUS_OPEN)
                ->count(),
            'processingCount' => Order::query()
                ->where('branch_id', $branchId)
                ->where('status', Order::STATUS_PROCESSING)
                ->count(),
            'readyCount' => Order::query()
                ->where('branch_id', $branchId)
                ->where('status', Order::STATUS_READY)
                ->count(),
            'todayCount' => (clone $todayPayments)->count(),
            'todaySales' => (float) (clone $todayPayments)->sum('amount'),
            'recentOrders' => Order::query()
                ->where('branch_id', $branchId)
                ->whereIn('status', Order::ACTIVE_STATUSES)
                ->withSum(['payments as paid_amount' => fn ($query) => $query->where('status', Payment::STATUS_PAID)], 'amount')
                ->latest('updated_at')
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
