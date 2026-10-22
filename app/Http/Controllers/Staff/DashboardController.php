<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\Menu;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('staff.dashboard', [
            'branch' => $user->branch,
            'menuCount' => Menu::query()
                ->where('is_active', true)
                ->whereHas('variants', fn ($query) => $query->where('is_active', true))
                ->count(),
            'stockCount' => $user->branch_id
                ? (int) BranchStock::where('branch_id', $user->branch_id)->sum('quantity')
                : 0,
        ]);
    }
}
