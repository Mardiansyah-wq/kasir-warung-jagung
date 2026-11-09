<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\StockMovement;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $branch = $user->branch;

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
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $movements = StockMovement::query()
            ->when($branch, fn ($query) => $query->where('branch_id', $branch->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->with(['variant.menu', 'user'])
            ->latest('occurred_at')
            ->latest('id')
            ->limit(30)
            ->get();

        return view('staff.stock.index', [
            'menus' => $menus,
            'branch' => $branch,
            'movements' => $movements,
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $stock = $orders->addStock(
            $request->user(),
            (int) $data['variant_id'],
            (int) $data['quantity'],
            $data['note'] ?? null,
        );

        return redirect()
            ->back()
            ->with('status', 'Stok ditambahkan. Stok sekarang: '.$stock->quantity.'.');
    }
}
