<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\DailyReportService;
use App\Support\ReportRange;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DailyReportService $reports): View
    {
        $range = ReportRange::fromRequest($request);
        $branchId = $this->branchId($request);

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
            'discountsEnabled' => Setting::discountsEnabled(),
            'summary' => $reports->buildRange($range, $branchId),
            'branches' => Branch::query()->orderBy('name')->get(),
            'branchId' => $branchId,
        ]);
    }

    /**
     * Cabang terpilih dari query string. Nilai yang tidak dikenal diabaikan
     * supaya dashboard tetap menampilkan semua cabang.
     */
    private function branchId(Request $request): ?int
    {
        $branchId = (int) $request->query('branch', 0);

        if ($branchId < 1) {
            return null;
        }

        return Branch::query()->whereKey($branchId)->exists() ? $branchId : null;
    }
}
