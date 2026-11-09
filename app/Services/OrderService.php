<?php

namespace App\Services;

use App\Models\BranchStock;
use App\Models\CashMovement;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderEditLog;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function currentDraft(User $user): ?Order
    {
        return Order::query()
            ->where('status', Order::STATUS_DRAFT)
            ->where('created_by', $user->id)
            ->where('branch_id', $user->branch_id)
            ->latest('id')
            ->first();
    }

    public function addItem(User $user, int $variantId, int $quantity, bool $withTopping): Order
    {
        return DB::transaction(function () use ($user, $variantId, $quantity, $withTopping): Order {
            $order = $this->draftForUpdate($user);
            $variant = MenuVariant::query()->with('menu')->findOrFail($variantId);

            if (! $variant->is_active || ! $variant->menu?->is_active) {
                throw ValidationException::withMessages([
                    'variant_id' => 'Menu atau varian ini sedang tidak aktif.',
                ]);
            }

            $toppingPrice = $withTopping && $variant->menu->hasToppingOption()
                ? $variant->menu->toppingPrice()
                : 0.0;

            $existing = $order->items()
                ->where('menu_variant_id', $variant->id)
                ->where('topping_price', $toppingPrice)
                ->first();

            if ($existing) {
                $quantity += $existing->quantity;

                $existing->update([
                    'quantity' => $quantity,
                    'line_total' => ((float) $existing->unit_price + (float) $existing->topping_price) * $quantity,
                ]);
            } else {
                $unitPrice = (float) $variant->selling_price;

                $order->items()->create([
                    'menu_variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->menu->name,
                    'variant_name_snapshot' => $variant->displayName(),
                    'topping_price' => $toppingPrice,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => ($unitPrice + $toppingPrice) * $quantity,
                ]);
            }

            $this->recalculate($order);

            $this->log($order, $user, 'item_added', null, [
                'menu_variant_id' => $variant->id,
                'quantity' => $quantity,
                'topping_price' => $toppingPrice,
            ]);

            return $order->fresh(['items']);
        });
    }

    public function updateItem(User $user, OrderItem $item, int $quantity, bool $withTopping): Order
    {
        return DB::transaction(function () use ($user, $item, $quantity, $withTopping): Order {
            $order = $this->editableOrderForItem($user, $item);

            $variant = $item->variant()->with('menu')->firstOrFail();
            $toppingPrice = $withTopping && $variant->menu->hasToppingOption()
                ? $variant->menu->toppingPrice()
                : 0.0;

            $before = [
                'quantity' => $item->quantity,
                'topping_price' => (float) $item->topping_price,
            ];

            $item->update([
                'topping_price' => $toppingPrice,
                'quantity' => $quantity,
                'line_total' => ((float) $item->unit_price + $toppingPrice) * $quantity,
            ]);

            $this->recalculate($order);

            $this->log($order, $user, 'item_updated', $before, [
                'quantity' => $quantity,
                'topping_price' => $toppingPrice,
            ]);

            return $order->fresh(['items']);
        });
    }

    public function removeItem(User $user, OrderItem $item): Order
    {
        return DB::transaction(function () use ($user, $item): Order {
            $order = $this->editableOrderForItem($user, $item);

            $before = [
                'menu_variant_id' => $item->menu_variant_id,
                'quantity' => $item->quantity,
                'line_total' => (float) $item->line_total,
            ];

            $item->delete();

            $this->recalculate($order);

            $this->log($order, $user, 'item_removed', $before, null);

            return $order->fresh(['items']);
        });
    }

    public function applyDiscount(User $user, float $discount, ?string $reason): Order
    {
        return DB::transaction(function () use ($user, $discount, $reason): Order {
            $order = $this->draftForUpdate($user);
            $subtotal = (float) $order->items()->sum('line_total');

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Diskon tidak boleh melebihi subtotal.',
                ]);
            }

            $before = ['discount_amount' => (float) $order->discount_amount];

            $order->update(['discount_amount' => $discount]);
            $this->recalculate($order);

            $this->log($order, $user, 'discount_changed', $before, [
                'discount_amount' => $discount,
            ], $reason);

            return $order->fresh(['items']);
        });
    }

    /**
     * @param  array{payment_method: string, amount_tendered?: float|int|string|null, reference_code?: string|null}  $data
     */
    public function finalize(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data): Order {
            $order = $this->draftForUpdate($user);
            $order->load('items');

            if ($order->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Keranjang masih kosong.',
                ]);
            }

            $this->ensureStockAvailable($order);

            foreach ($order->items as $item) {
                $stock = BranchStock::query()
                    ->where('branch_id', $order->branch_id)
                    ->where('menu_variant_id', $item->menu_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stock->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'branch_id' => $order->branch_id,
                    'menu_variant_id' => $item->menu_variant_id,
                    'user_id' => $user->id,
                    'movement_type' => 'out',
                    'quantity' => $item->quantity,
                    'note' => 'Penjualan '.$order->order_code,
                    'occurred_at' => now(),
                ]);
            }

            $this->recalculate($order);
            $order->refresh();

            $method = $data['payment_method'];
            $total = (float) $order->total_amount;

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Total transaksi harus lebih dari nol.',
                ]);
            }

            $tendered = $method === 'cash'
                ? (float) ($data['amount_tendered'] ?? 0)
                : $total;

            if ($method === 'cash' && $tendered < $total) {
                throw ValidationException::withMessages([
                    'amount_tendered' => 'Uang diterima kurang dari total tagihan.',
                ]);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $method,
                'amount' => $total,
                'amount_tendered' => $tendered,
                'change_amount' => $method === 'cash' ? $tendered - $total : 0,
                'status' => Payment::STATUS_PAID,
                'reference_code' => $data['reference_code'] ?? null,
                'paid_at' => now(),
            ]);

            if ($method === 'cash') {
                CashMovement::create([
                    'branch_id' => $order->branch_id,
                    'user_id' => $user->id,
                    'movement_type' => 'in',
                    'category' => CashMovement::CATEGORY_SALE,
                    'amount' => $total,
                    'description' => 'Penjualan '.$order->order_code,
                    'related_payment_id' => $payment->id,
                    'occurred_at' => now(),
                ]);
            }

            $order->update([
                'status' => Order::STATUS_FINALIZED,
                'finalized_by' => $user->id,
                'finalized_at' => now(),
            ]);

            $this->log($order, $user, 'finalized', null, [
                'payment_method' => $method,
                'total_amount' => $total,
            ]);

            return $order->fresh(['items', 'payments']);
        });
    }

    public function updateNotes(User $user, ?string $notes): Order
    {
        return DB::transaction(function () use ($user, $notes): Order {
            $order = $this->draftForUpdate($user);
            $before = ['notes' => $order->notes];

            $order->update(['notes' => $notes]);

            $this->log($order, $user, 'other', $before, ['notes' => $notes], 'Catatan pesanan diubah');

            return $order->fresh(['items']);
        });
    }

    public function cancel(User $user): Order
    {
        return DB::transaction(function () use ($user): Order {
            $order = $this->findDraftForUpdate($user);

            if (! $order) {
                throw ValidationException::withMessages([
                    'order' => 'Tidak ada keranjang aktif untuk dibatalkan.',
                ]);
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            $this->log($order, $user, 'cancelled');

            return $order->fresh(['items']);
        });
    }

    public function addStock(User $user, int $variantId, int $quantity, ?string $note): BranchStock
    {
        if (! $user->branch_id) {
            throw ValidationException::withMessages([
                'stock' => 'Akun staff belum terhubung ke cabang.',
            ]);
        }

        return DB::transaction(function () use ($user, $variantId, $quantity, $note): BranchStock {
            MenuVariant::query()->findOrFail($variantId);

            $stock = BranchStock::query()
                ->where('branch_id', $user->branch_id)
                ->where('menu_variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = BranchStock::create([
                    'branch_id' => $user->branch_id,
                    'menu_variant_id' => $variantId,
                    'quantity' => 0,
                ]);
            }

            $stock->increment('quantity', $quantity);

            StockMovement::create([
                'branch_id' => $user->branch_id,
                'menu_variant_id' => $variantId,
                'user_id' => $user->id,
                'movement_type' => 'in',
                'quantity' => $quantity,
                'note' => $note,
                'occurred_at' => now(),
            ]);

            return $stock->refresh();
        });
    }

    private function findDraftForUpdate(User $user): ?Order
    {
        $this->ensureStaffBranch($user);

        return Order::query()
            ->where('status', Order::STATUS_DRAFT)
            ->where('created_by', $user->id)
            ->where('branch_id', $user->branch_id)
            ->latest('id')
            ->lockForUpdate()
            ->first();
    }

    private function draftForUpdate(User $user): Order
    {
        $order = $this->findDraftForUpdate($user);

        if ($order) {
            return $order;
        }

        $order = Order::create([
            'order_code' => $this->generateOrderCode(),
            'branch_id' => $user->branch_id,
            'created_by' => $user->id,
            'status' => Order::STATUS_DRAFT,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);

        $this->log($order, $user, 'created');

        return $order;
    }

    private function editableOrderForItem(User $user, OrderItem $item): Order
    {
        $order = $this->draftForUpdate($user);

        if ((int) $item->order_id !== (int) $order->id) {
            throw ValidationException::withMessages([
                'item' => 'Item tidak ditemukan pada keranjang aktif.',
            ]);
        }

        return $order;
    }

    private function ensureStaffBranch(User $user): void
    {
        if (! $user->branch_id) {
            throw ValidationException::withMessages([
                'branch' => 'Akun staff belum terhubung ke cabang.',
            ]);
        }
    }

    private function ensureStockAvailable(Order $order): void
    {
        $shortages = [];
        $variants = MenuVariant::query()
            ->with('menu')
            ->whereIn('id', $order->items->pluck('menu_variant_id'))
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $stock = BranchStock::query()
                ->where('branch_id', $order->branch_id)
                ->where('menu_variant_id', $item->menu_variant_id)
                ->lockForUpdate()
                ->first();

            $available = (int) ($stock?->quantity ?? 0);

            if ($available < $item->quantity) {
                $variant = $variants->get($item->menu_variant_id);
                $label = $variant
                    ? $variant->menu->name.' — '.$variant->displayName()
                    : $item->product_name_snapshot.' — '.$item->variant_name_snapshot;

                $shortages[] = $label.' (butuh '.$item->quantity.', stok '.$available.')';
            }
        }

        if ($shortages !== []) {
            throw ValidationException::withMessages([
                'stock' => 'Stok tidak cukup: '.implode('; ', $shortages).'. Tambah stok terlebih dahulu.',
            ]);
        }
    }

    private function recalculate(Order $order): void
    {
        $subtotal = (float) $order->items()->sum('line_total');
        $discount = min((float) $order->discount_amount, $subtotal);

        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $subtotal - $discount,
        ]);
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'WJT-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function log(
        Order $order,
        User $user,
        string $action,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
    ): void {
        OrderEditLog::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'action_type' => $action,
            'before_data' => $before,
            'after_data' => $after,
            'reason' => $reason,
        ]);
    }
}
