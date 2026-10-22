<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        if (! in_array($category, Menu::CATEGORIES, true)) {
            $category = null;
        }

        $branch = $request->user()->branch;

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

        return view('staff.menus.index', [
            'menus' => $menus,
            'branch' => $branch,
            'category' => $category,
        ]);
    }
}
