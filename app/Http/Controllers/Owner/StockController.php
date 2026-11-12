<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::query()->orderBy('name')->get();
        $branchId = (int) $request->query('branch_id', 0);
        $branch = $branches->firstWhere('id', $branchId) ?? $branches->first();

        $menus = collect();

        if ($branch) {
            $menus = Menu::query()
                ->where('is_active', true)
                ->whereHas('variants', fn ($query) => $query->where('is_active', true))
                ->with(['variants' => function ($query) use ($branch) {
                    $query->where('is_active', true)
                        ->orderBy('selling_price')
                        ->with([
                            'stocks' => fn ($stocks) => $stocks->where('branch_id', $branch->id),
                        ]);
                }])
                ->orderBy('category')
                ->orderBy('name')
                ->get();
        }

        return view('owner.stock.index', [
            'branches' => $branches,
            'branch' => $branch,
            'menus' => $menus,
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'mode' => ['required', Rule::in([
                OrderService::STOCK_ADD,
                OrderService::STOCK_REDUCE,
                OrderService::STOCK_SET,
            ])],
            'quantity' => [
                'required',
                'integer',
                Rule::when(
                    $request->input('mode') === OrderService::STOCK_SET,
                    ['min:0'],
                    ['min:1'],
                ),
                'max:999999',
            ],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $stock = $orders->adjustStock(
            (int) $data['branch_id'],
            $request->user(),
            (int) $data['variant_id'],
            (string) $data['mode'],
            (int) $data['quantity'],
            $data['note'] ?? null,
        );

        $variant = MenuVariant::query()->with('menu')->find((int) $data['variant_id']);

        return redirect()
            ->route('owner.stock.index', ['branch_id' => $data['branch_id']])
            ->with('status', 'Stok '.($variant?->menu?->name ?? 'menu').' — '.($variant?->displayName() ?? 'varian')
                .' diperbarui. Stok sekarang: '.$stock->quantity.'.');
    }
}
