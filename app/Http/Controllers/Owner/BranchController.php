<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::orderBy('created_at', 'desc')->get();

        return view('owner.branches.index', [
            'branches' => $branches,
        ]);
    }

    public function create(): View
    {
        return view('owner.branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:branches,name'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        Branch::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'is_active' => true,
        ]);

        return redirect()->route('owner.branches.index')->with('success', '✅ Cabang baru berhasil ditambahkan.');
    }

    public function edit(Branch $branch): View
    {
        return view('owner.branches.edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:branches,name,'.$branch->id],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $branch->update([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
        ]);

        return redirect()->route('owner.branches.index')->with('success', '✅ Data cabang berhasil diperbarui.');
    }

    public function toggle(Branch $branch): RedirectResponse
    {
        if ($branch->is_active) {
            $activeStaff = $branch->users()->where('is_active', true)->count();
            $activeOrders = Order::query()
                ->where('branch_id', $branch->id)
                ->whereIn('status', Order::ACTIVE_STATUSES)
                ->count();

            if ($activeStaff > 0 || $activeOrders > 0) {
                $reason = collect([
                    $activeStaff > 0 ? $activeStaff.' staff aktif' : null,
                    $activeOrders > 0 ? $activeOrders.' pesanan berjalan' : null,
                ])->filter()->implode(' dan ');

                return redirect()->route('owner.branches.index')->with(
                    'error',
                    'Cabang tidak bisa dinonaktifkan karena masih ada '.$reason.'.',
                );
            }
        }

        $branch->update(['is_active' => ! $branch->is_active]);

        return redirect()->route('owner.branches.index')->with(
            'success',
            $branch->is_active ? '✅ Cabang berhasil diaktifkan.' : '✅ Cabang berhasil dinonaktifkan.',
        );
    }
}
