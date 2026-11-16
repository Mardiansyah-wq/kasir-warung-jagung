<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
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

        return view('owner.dashboard', [
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
