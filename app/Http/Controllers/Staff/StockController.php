<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\StockMovement;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $user = $request->user();

        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'mode' => ['nullable', Rule::in([OrderService::STOCK_ADD, OrderService::STOCK_REDUCE])],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'note' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($request->input('mode') === OrderService::STOCK_REDUCE),
            ],
        ], [
            'note.required' => 'Catatan wajib diisi saat mengurangi stok.',
            'mode.in' => 'Jenis perubahan stok tidak valid.',
        ]);

        if (! $user->branch_id) {
            return back()->withErrors([
                'stock' => 'Akun staff belum terhubung ke cabang.',
            ]);
        }

        $mode = (string) ($data['mode'] ?? OrderService::STOCK_ADD);

        $stock = $orders->adjustStock(
            (int) $user->branch_id,
            $user,
            (int) $data['variant_id'],
            $mode,
            (int) $data['quantity'],
            $data['note'] ?? null,
        );

        return redirect()
            ->back()
            ->with(
                'status',
                ($mode === OrderService::STOCK_REDUCE ? 'Stok dikurangi. ' : 'Stok ditambahkan. ')
                .'Stok sekarang: '.$stock->quantity.'.',
            );
    }
}
