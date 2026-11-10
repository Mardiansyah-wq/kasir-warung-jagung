<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
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
        $search = trim($request->string('search')->toString());

        $orders = Order::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('order_code', 'like', '%'.$search.'%');

                    if (ctype_digit($search)) {
                        $query->orWhere('queue_number', (int) $search);
                    }
                });
            })
            ->when($request->filled('date'), fn ($query) => $query->whereDate('created_at', $request->date('date')))
            ->with(['branch', 'creator', 'payments'])
            ->withSum(['payments as paid_amount' => fn ($query) => $query->where('status', Payment::STATUS_PAID)], 'amount')
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $todayPayments = Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereDate('paid_at', today());

        return view('owner.orders.index', [
            'orders' => $orders,
            'branches' => Branch::query()->orderBy('name')->get(),
            'status' => $status,
            'branchId' => $branchId,
            'search' => $search,
            'date' => $request->string('date')->toString(),
            'todaySales' => (float) (clone $todayPayments)->sum('amount'),
            'todayCount' => (clone $todayPayments)->count(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.variant.menu', 'payments.receiver', 'creator', 'branch']);

        return view('owner.orders.show', [
            'order' => $order,
        ]);
    }
}
