<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request, OrderService $orders): View
    {
        $user = $request->user();
        $branch = $user->branch;

        $category = $request->query('category');

        if (! in_array($category, Menu::CATEGORIES, true)) {
            $category = null;
        }

        $menus = Menu::query()
            ->where('is_active', true)
            ->whereHas('variants', fn ($query) => $query->where('is_active', true))
            ->with(['variants' => function ($query) use ($branch) {
                $query->where('is_active', true)->orderBy('selling_price');

                if ($branch) {
                    $query->with([
                        'stocks' => fn ($stocks) => $stocks->where('branch_id', $branch->id),
                    ]);
                }
            }])
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $draft = $orders->currentDraft($user);
        $draft?->load(['items.variant.menu']);

        return view('staff.pos.index', [
            'menus' => $menus,
            'branch' => $branch,
            'category' => $category,
            'draft' => $draft,
        ]);
    }
}
