<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->query('status');

        if (! in_array($status, Order::STATUSES, true)) {
            $status = null;
        }

        $search = trim($request->string('search')->toString());

        $orders = Order::query()
            ->where('branch_id', $user->branch_id)
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
            ->with(['items', 'payments', 'creator'])
            ->withSum(['payments as paid_amount' => fn ($query) => $query->where('status', Payment::STATUS_PAID)], 'amount')
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('staff.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'date' => $request->string('date')->toString(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load(['items.variant.menu', 'payments.receiver', 'creator', 'branch']);

        return view('staff.orders.show', [
            'order' => $order,
        ]);
    }

    public function storeItem(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'with_topping' => ['sometimes', 'boolean'],
        ]);

        try {
            $orders->addItem(
                $request->user(),
                $order,
                (int) $data['variant_id'],
                (int) $data['quantity'],
                (bool) ($data['with_topping'] ?? false),
            );
        } catch (ValidationException $exception) {
            $this->flashStockShortage($request, $exception);

            throw $exception;
        }

        return redirect()
            ->route('staff.pos.show', $order)
            ->with('status', 'Item ditambahkan ke pesanan '.$order->queueLabel().'.');
    }

    public function updateItem(Request $request, Order $order, OrderItem $item, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'with_topping' => ['sometimes', 'boolean'],
        ]);

        try {
            $orders->updateItem(
                $request->user(),
                $order,
                $item,
                (int) $data['quantity'],
                (bool) ($data['with_topping'] ?? false),
            );
        } catch (ValidationException $exception) {
            $this->flashStockShortage($request, $exception);

            throw $exception;
        }

        return redirect()
            ->route('staff.pos.show', $order)
            ->with('status', 'Item pesanan diperbarui.');
    }

    public function destroyItem(Request $request, Order $order, OrderItem $item, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $orders->removeItem($request->user(), $order, $item);

        return redirect()
            ->route('staff.pos.show', $order)
            ->with('status', 'Item dihapus dari pesanan.');
    }

    public function applyDiscount(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        abort_unless(config('pos.discounts_enabled'), 404);

        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $orders->applyDiscount(
            $request->user(),
            $order,
            (float) ($data['discount_amount'] ?? 0),
            $data['reason'] ?? null,
        );

        return redirect()
            ->route('staff.pos.show', $order)
            ->with('status', 'Diskon diperbarui.');
    }

    public function updateNotes(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $orders->updateNotes($request->user(), $order, $data['notes'] ?? null);

        return redirect()
            ->route('staff.pos.show', $order)
            ->with('status', 'Catatan pesanan disimpan.');
    }

    public function pay(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'qris', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0', 'max:9999999999', 'required_if:payment_method,cash'],
            'reference_code' => ['nullable', 'string', 'max:100'],
        ]);

        $order = $orders->addPayment($request->user(), $order, [
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'amount_tendered' => $data['amount_tendered'] ?? null,
            'reference_code' => $data['reference_code'] ?? null,
        ]);

        return redirect()
            ->route('staff.orders.show', $order)
            ->with('status', 'Pembayaran Rp '.number_format((float) $data['amount'], 0, ',', '.').' diterima.');
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'status' => ['required', Rule::in([Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_COMPLETED])],
        ]);

        $order = $orders->updateStatus($request->user(), $order, $data['status']);

        return redirect()
            ->back()
            ->with('status', 'Status pesanan '.$order->queueLabel().' menjadi '.$order->statusLabel().'.');
    }

    public function cancel(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $orders->cancel($request->user(), $order);

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Pesanan '.$order->queueLabel().' dibatalkan.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        if (! $user->branch_id || (int) $order->branch_id !== (int) $user->branch_id) {
            abort(403);
        }
    }

    private function flashStockShortage(Request $request, ValidationException $exception): void
    {
        if (isset($exception->errors()['stock'])) {
            $request->session()->flash('stock_shortage', true);
        }
    }
}
