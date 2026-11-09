<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
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

        $orders = Order::query()
            ->where('branch_id', $user->branch_id)
            ->where(function ($query) use ($user) {
                $query->where('status', '!=', Order::STATUS_DRAFT)
                    ->orWhere('created_by', $user->id);
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('order_code', 'like', '%'.$request->string('search').'%');
            })
            ->with(['items', 'payments'])
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('staff.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load(['items.variant.menu', 'payments', 'creator', 'finalizer', 'branch']);

        return view('staff.orders.show', [
            'order' => $order,
        ]);
    }

    public function storeItem(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'with_topping' => ['sometimes', 'boolean'],
        ]);

        $orders->addItem(
            $request->user(),
            (int) $data['variant_id'],
            (int) $data['quantity'],
            (bool) ($data['with_topping'] ?? false),
        );

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Item ditambahkan ke keranjang.');
    }

    public function updateItem(Request $request, OrderItem $item, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'with_topping' => ['sometimes', 'boolean'],
        ]);

        $orders->updateItem(
            $request->user(),
            $item,
            (int) $data['quantity'],
            (bool) ($data['with_topping'] ?? false),
        );

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Item keranjang diperbarui.');
    }

    public function destroyItem(Request $request, OrderItem $item, OrderService $orders): RedirectResponse
    {
        $orders->removeItem($request->user(), $item);

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Item dihapus dari keranjang.');
    }

    public function applyDiscount(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $orders->applyDiscount(
            $request->user(),
            (float) ($data['discount_amount'] ?? 0),
            $data['reason'] ?? null,
        );

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Diskon diperbarui.');
    }

    public function updateNotes(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $orders->updateNotes($request->user(), $data['notes'] ?? null);

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Catatan pesanan disimpan.');
    }

    public function finalize(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'qris', 'transfer'])],
            'amount_tendered' => ['nullable', 'numeric', 'min:0', 'max:9999999999', 'required_if:payment_method,cash'],
            'reference_code' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $order = $orders->finalize($request->user(), [
                'payment_method' => $data['payment_method'],
                'amount_tendered' => $data['amount_tendered'] ?? null,
                'reference_code' => $data['reference_code'] ?? null,
            ]);
        } catch (ValidationException $exception) {
            if (isset($exception->errors()['stock'])) {
                $request->session()->flash('stock_shortage', true);
            }

            throw $exception;
        }

        return redirect()
            ->route('staff.orders.show', $order)
            ->with('status', 'Pembayaran berhasil. Transaksi '.$order->order_code.' sudah lunas.');
    }

    public function cancel(Request $request, OrderService $orders): RedirectResponse
    {
        $orders->cancel($request->user());

        return redirect()
            ->route('staff.pos.index')
            ->with('status', 'Keranjang dibatalkan.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        if (! $user->branch_id || (int) $order->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        if ($order->isDraft() && (int) $order->created_by !== (int) $user->id) {
            abort(403);
        }
    }
}
