<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('owner.dashboard', [
            'branchCount' => Branch::where('is_active', true)->count(),
        ]);
    }
}
