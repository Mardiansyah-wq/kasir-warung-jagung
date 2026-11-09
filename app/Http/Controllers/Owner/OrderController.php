<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        if (! in_array($status, Order::STATUSES, true)) {
            $status = null;
        }

        $branchId = $request->integer('branch_id') ?: null;

        $orders = Order::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('order_code', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('date'), fn ($query) => $query->whereDate('created_at', $request->date('date')))
            ->with(['branch', 'creator', 'payments'])
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $todayOrders = Order::query()
            ->where('status', Order::STATUS_FINALIZED)
            ->whereDate('finalized_at', today());

        return view('owner.orders.index', [
            'orders' => $orders,
            'branches' => Branch::query()->orderBy('name')->get(),
            'status' => $status,
            'branchId' => $branchId,
            'search' => $request->string('search')->toString(),
            'date' => $request->string('date')->toString(),
            'todaySales' => (float) (clone $todayOrders)->sum('total_amount'),
            'todayCount' => (clone $todayOrders)->count(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.variant.menu', 'payments', 'creator', 'finalizer', 'branch']);

        return view('owner.orders.show', [
            'order' => $order,
        ]);
    }
}
