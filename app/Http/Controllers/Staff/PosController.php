<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $branch = $user->branch;

        $status = $request->query('status', 'active');

        if (! in_array($status, array_merge(['active', 'all'], Order::STATUSES), true)) {
            $status = 'active';
        }

        $search = trim($request->string('search')->toString());

        $orders = Order::query()
            ->where('branch_id', $user->branch_id)
            ->when($status === 'active', fn ($query) => $query->whereIn('status', Order::ACTIVE_STATUSES))
            ->when($status === 'open' || in_array($status, Order::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('order_code', 'like', '%'.$search.'%');

                    if (ctype_digit($search)) {
                        $query->orWhere('queue_number', (int) $search);
                    }
                });
            })
            ->with(['items', 'payments', 'creator'])
            ->withSum(['payments as paid_amount' => fn ($query) => $query->where('status', Payment::STATUS_PAID)], 'amount')
            ->withCount('items')
            ->latest('updated_at')
            ->latest('id')
            ->limit(100)
            ->get();

        $counts = [
            'active' => Order::where('branch_id', $user->branch_id)->whereIn('status', Order::ACTIVE_STATUSES)->count(),
            'open' => Order::where('branch_id', $user->branch_id)->where('status', Order::STATUS_OPEN)->count(),
            'processing' => Order::where('branch_id', $user->branch_id)->where('status', Order::STATUS_PROCESSING)->count(),
            'ready' => Order::where('branch_id', $user->branch_id)->where('status', Order::STATUS_READY)->count(),
            'completed' => Order::where('branch_id', $user->branch_id)->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled' => Order::where('branch_id', $user->branch_id)->where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('staff.pos.index', [
            'orders' => $orders,
            'branch' => $branch,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'order_type' => ['required', Rule::in(Order::ORDER_TYPES)],
        ], [
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'order_type.required' => 'Tipe pesanan wajib dipilih.',
        ]);

        $order = $orders->createOrder($request->user(), [
            'customer_name' => $data['customer_name'],
            'order_type' => $data['order_type'],
        ]);

        return redirect()
            ->route('staff.pos.show', $order)
            ->with('status', 'Pesanan '.$order->queueLabel().' dibuat untuk '.$order->customer_name.'.');
    }

    public function show(Request $request, Order $order): View
    {
        $user = $request->user();

        if (! $user->branch_id || (int) $order->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        $order->load(['items.variant.menu', 'payments.receiver', 'creator', 'branch']);

        $category = $request->query('category');

        if (! in_array($category, Menu::CATEGORIES, true)) {
            $category = null;
        }

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
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('staff.pos.show', [
            'order' => $order,
            'menus' => $menus,
            'branch' => $branch,
            'category' => $category,
        ]);
    }
}
