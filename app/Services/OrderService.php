<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Branch;
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
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const STOCK_ADD = 'in';

    public const STOCK_REDUCE = 'out';

    public const STOCK_SET = 'set';

    public function __construct(private readonly ActivityLogger $activityLogger) {}

    /**
     * @param  array{customer_name: string, order_type: string}  $data
     */
    public function createOrder(User $user, array $data): Order
    {
        $this->ensureStaffBranch($user);

        return DB::transaction(function () use ($user, $data): Order {
            $businessDate = now()->toDateString();
            $queueNumber = $this->nextQueueNumber((int) $user->branch_id, $businessDate);

            $order = Order::create([
                'order_code' => $this->generateOrderCode((int) $user->branch_id, $businessDate, $queueNumber),
                'queue_number' => $queueNumber,
                'business_date' => $businessDate,
                'customer_name' => $data['customer_name'],
                'order_type' => $data['order_type'],
                'branch_id' => $user->branch_id,
                'created_by' => $user->id,
                'status' => Order::STATUS_OPEN,
                'subtotal' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
            ]);

            $this->log($order, $user, 'created', null, [
                'queue_number' => $queueNumber,
                'customer_name' => $order->customer_name,
                'order_type' => $order->order_type,
            ]);

            return $order;
        });
    }

    public function addItem(User $user, Order $order, int $variantId, int $quantity, bool $withTopping): Order
    {
        return DB::transaction(function () use ($user, $order, $variantId, $quantity, $withTopping): Order {
            $order = $this->lockOrder($user, $order);
            $this->ensureCanAddItems($order);

            $variant = MenuVariant::query()->with('menu')->findOrFail($variantId);

            if (! $variant->is_active || ! $variant->menu?->is_active) {
                throw ValidationException::withMessages([
                    'variant_id' => 'Menu atau varian ini sedang tidak aktif.',
                ]);
            }

            $toppingPrice = $withTopping && $variant->menu->hasToppingOption()
                ? $variant->menu->toppingPrice()
                : 0.0;

            $hasPaidPayment = $order->hasPaidPayment();
            $unitPrice = (float) $variant->selling_price;

            if (! $hasPaidPayment) {
                $existing = $order->items()
                    ->where('menu_variant_id', $variant->id)
                    ->where('topping_price', $toppingPrice)
                    ->where('is_addon', false)
                    ->first();

                if ($existing) {
                    $newQuantity = $existing->quantity + $quantity;

                    $existing->update([
                        'quantity' => $newQuantity,
                        'line_total' => ((float) $existing->unit_price + (float) $existing->topping_price) * $newQuantity,
                    ]);
                } else {
                    $order->items()->create([
                        'menu_variant_id' => $variant->id,
                        'product_name_snapshot' => $variant->menu->name,
                        'variant_name_snapshot' => $variant->displayName(),
                        'topping_price' => $toppingPrice,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'is_addon' => false,
                        'line_total' => ($unitPrice + $toppingPrice) * $quantity,
                    ]);
                }
            } else {
                $order->items()->create([
                    'menu_variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->menu->name,
                    'variant_name_snapshot' => $variant->displayName(),
                    'topping_price' => $toppingPrice,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'is_addon' => true,
                    'line_total' => ($unitPrice + $toppingPrice) * $quantity,
                ]);
            }

            $this->takeStock(
                $order,
                $user,
                $variant->id,
                $quantity,
                'Pesanan '.$order->queueLabel().' · '.$variant->menu->name.' '.$variant->displayName(),
            );

            if ($hasPaidPayment && $order->isReady()) {
                $order->update(['status' => Order::STATUS_PROCESSING]);

                $this->log($order, $user, 'status_changed', ['status' => Order::STATUS_READY], [
                    'status' => Order::STATUS_PROCESSING,
                    'reason' => 'Item tambahan',
                ]);
            }

            $this->recalculate($order);

            $this->log($order, $user, 'item_added', null, [
                'menu_variant_id' => $variant->id,
                'quantity' => $quantity,
                'topping_price' => $toppingPrice,
                'is_addon' => $hasPaidPayment,
            ]);

            return $order->fresh(['items', 'payments']);
        });
    }

    public function updateItem(User $user, Order $order, OrderItem $item, int $quantity, bool $withTopping): Order
    {
        return DB::transaction(function () use ($user, $order, $item, $quantity, $withTopping): Order {
            $order = $this->lockOrder($user, $order);
            $this->ensureItemBelongsToOrder($order, $item);
            $this->ensureOrderIsEditable($order);

            $variant = $item->variant()->with('menu')->firstOrFail();
            $toppingPrice = $withTopping && $variant->menu->hasToppingOption()
                ? $variant->menu->toppingPrice()
                : 0.0;

            $quantityDelta = $quantity - (int) $item->quantity;

            if ($quantityDelta > 0) {
                $this->takeStock(
                    $order,
                    $user,
                    (int) $item->menu_variant_id,
                    $quantityDelta,
                    'Tambahan pesanan '.$order->queueLabel(),
                );
            } elseif ($quantityDelta < 0) {
                $this->returnStock(
                    $order,
                    $user,
                    (int) $item->menu_variant_id,
                    abs($quantityDelta),
                    'Koreksi pesanan '.$order->queueLabel(),
                );
            }

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

            return $order->fresh(['items', 'payments']);
        });
    }

    public function removeItem(User $user, Order $order, OrderItem $item): Order
    {
        return DB::transaction(function () use ($user, $order, $item): Order {
            $order = $this->lockOrder($user, $order);
            $this->ensureItemBelongsToOrder($order, $item);
            $this->ensureOrderIsEditable($order);

            $before = [
                'menu_variant_id' => $item->menu_variant_id,
                'quantity' => $item->quantity,
                'line_total' => (float) $item->line_total,
            ];

            $this->returnStock(
                $order,
                $user,
                (int) $item->menu_variant_id,
                (int) $item->quantity,
                'Hapus item pesanan '.$order->queueLabel(),
            );

            $item->delete();

            $this->recalculate($order);

            $this->log($order, $user, 'item_removed', $before, null);

            return $order->fresh(['items', 'payments']);
        });
    }

    public function applyDiscount(User $user, Order $order, float $discount, ?string $reason): Order
    {
        return DB::transaction(function () use ($user, $order, $discount, $reason): Order {
            $order = $this->lockOrder($user, $order);
            $this->ensureOrderIsEditable($order);

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

            return $order->fresh(['items', 'payments']);
        });
    }

    public function updateNotes(User $user, Order $order, ?string $notes): Order
    {
        return DB::transaction(function () use ($user, $order, $notes): Order {
            $order = $this->lockOrder($user, $order);

            if (! $order->isActive()) {
                throw ValidationException::withMessages([
                    'notes' => 'Pesanan yang sudah selesai atau batal tidak bisa diubah.',
                ]);
            }

            $before = ['notes' => $order->notes];

            $order->update(['notes' => $notes]);

            $this->log($order, $user, 'other', $before, ['notes' => $notes], 'Catatan pesanan diubah');

            return $order->fresh(['items', 'payments']);
        });
    }

    /**
     * @param  array{payment_method: string, amount: float|int|string, amount_tendered?: float|int|string|null, reference_code?: string|null}  $data
     */
    public function addPayment(User $user, Order $order, array $data): Order
    {
        return DB::transaction(function () use ($user, $order, $data): Order {
            $order = $this->lockOrder($user, $order);
            $order->load(['items', 'payments']);

            if (! $order->isActive()) {
                throw ValidationException::withMessages([
                    'amount' => 'Pesanan yang sudah selesai atau batal tidak bisa dibayar.',
                ]);
            }

            if ($order->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Pesanan masih kosong.',
                ]);
            }

            $this->recalculate($order);
            $order->refresh();

            $due = $order->balanceDue();

            if ($due <= 0.0) {
                throw ValidationException::withMessages([
                    'amount' => 'Pesanan ini sudah lunas.',
                ]);
            }

            $amount = round((float) $data['amount'], 2);
            $method = $data['payment_method'];

            if ($amount <= 0.0) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran harus lebih dari nol.',
                ]);
            }

            if ($amount > $due + 0.001) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran melebihi sisa tagihan (Rp '.number_format($due, 0, ',', '.').').',
                ]);
            }

            $tendered = $method === 'cash'
                ? round((float) ($data['amount_tendered'] ?? 0), 2)
                : $amount;

            if ($method === 'cash' && $tendered < $amount) {
                throw ValidationException::withMessages([
                    'amount_tendered' => 'Uang diterima kurang dari jumlah pembayaran.',
                ]);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'received_by' => $user->id,
                'payment_method' => $method,
                'amount' => $amount,
                'amount_tendered' => $tendered,
                'change_amount' => $method === 'cash' ? $tendered - $amount : 0,
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
                    'amount' => $amount,
                    'description' => 'Pembayaran '.$order->queueLabel().' ('.$order->order_code.')',
                    'related_payment_id' => $payment->id,
                    'occurred_at' => now(),
                ]);
            }

            $order->refresh();
            $remaining = $order->balanceDue();

            if ($remaining <= 0.0 && ! $order->paid_at) {
                $order->update(['paid_at' => now()]);
            }

            $this->log($order, $user, 'paid', null, [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'payment_method' => $method,
                'remaining' => $remaining,
            ]);

            return $order->fresh(['items', 'payments']);
        });
    }

    public function updateStatus(User $user, Order $order, string $status): Order
    {
        return DB::transaction(function () use ($user, $order, $status): Order {
            $order = $this->lockOrder($user, $order);

            if ($order->status === $status) {
                return $order;
            }

            if ($order->isCompleted() || $order->isCancelled()) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan yang sudah selesai atau batal tidak bisa diubah statusnya.',
                ]);
            }

            $allowed = match ($status) {
                Order::STATUS_PROCESSING => in_array($order->status, [Order::STATUS_OPEN, Order::STATUS_READY], true),
                Order::STATUS_READY => in_array($order->status, [Order::STATUS_OPEN, Order::STATUS_PROCESSING], true),
                Order::STATUS_COMPLETED => $order->isActive(),
                default => false,
            };

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'status' => 'Status pesanan tidak valid untuk perubahan ini.',
                ]);
            }

            if ($status === Order::STATUS_COMPLETED && ! $order->isFullyPaid()) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan belum lunas, sisa Rp '.number_format($order->balanceDue(), 0, ',', '.').'.',
                ]);
            }

            $before = ['status' => $order->status];

            $order->update([
                'status' => $status,
                'completed_at' => $status === Order::STATUS_COMPLETED ? now() : $order->completed_at,
            ]);

            $this->log($order, $user, 'status_changed', $before, ['status' => $status]);

            return $order->fresh(['items', 'payments']);
        });
    }

    public function cancel(User $user, Order $order): Order
    {
        return DB::transaction(function () use ($user, $order): Order {
            $order = $this->lockOrder($user, $order);

            if (! $order->canCancel()) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan ini tidak bisa dibatalkan karena sudah ada pembayaran atau sudah selesai.',
                ]);
            }

            foreach ($order->items()->get() as $item) {
                $this->returnStock(
                    $order,
                    $user,
                    (int) $item->menu_variant_id,
                    (int) $item->quantity,
                    'Batal pesanan '.$order->queueLabel(),
                );
            }

            $before = ['status' => $order->status];

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            $this->log($order, $user, 'cancelled', $before, ['status' => Order::STATUS_CANCELLED]);

            return $order->fresh(['items', 'payments']);
        });
    }

    /**
     * Add, reduce, or correct the stock of a variant for a branch.
     *
     * Every change is written to the stock ledger and the activity log so the
     * owner can follow what staff did at each branch.
     */
    public function adjustStock(
        int $branchId,
        User $actor,
        int $variantId,
        string $mode,
        int $quantity,
        ?string $note = null,
    ): BranchStock {
        if (! in_array($mode, [self::STOCK_ADD, self::STOCK_REDUCE, self::STOCK_SET], true)) {
            throw ValidationException::withMessages([
                'mode' => 'Jenis penyesuaian stok tidak dikenali.',
            ]);
        }

        if ($quantity < 0 || ($mode !== self::STOCK_SET && $quantity < 1)) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah stok tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($branchId, $actor, $variantId, $mode, $quantity, $note): BranchStock {
            $branch = Branch::query()->findOrFail($branchId);
            $variant = MenuVariant::query()->with('menu')->findOrFail($variantId);

            $stock = BranchStock::query()
                ->where('branch_id', $branchId)
                ->where('menu_variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = BranchStock::create([
                    'branch_id' => $branchId,
                    'menu_variant_id' => $variantId,
                    'quantity' => 0,
                ]);
            }

            $before = (int) $stock->quantity;
            $after = match ($mode) {
                self::STOCK_ADD => $before + $quantity,
                self::STOCK_REDUCE => $before - $quantity,
                default => $quantity,
            };

            $label = ($variant->menu?->name ?? 'Menu').' — '.$variant->displayName();

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak cukup untuk '.$label.' (stok sekarang '.$before.').',
                ]);
            }

            if ($after === $before) {
                return $stock->refresh();
            }

            $stock->update(['quantity' => $after]);

            $delta = $after - $before;
            $movementNote = $note ?: match ($mode) {
                self::STOCK_ADD => 'Tambah stok manual',
                self::STOCK_REDUCE => 'Kurangi stok manual',
                default => 'Koreksi stok '.$before.' → '.$after,
            };

            StockMovement::create([
                'branch_id' => $branchId,
                'menu_variant_id' => $variantId,
                'user_id' => $actor->id,
                'movement_type' => match ($mode) {
                    self::STOCK_ADD => 'in',
                    self::STOCK_REDUCE => 'out',
                    default => 'adjustment',
                },
                'quantity' => abs($delta),
                'note' => $movementNote,
                'occurred_at' => now(),
            ]);

            $changes = [
                'quantity' => ['new' => $delta],
                'stock' => ['old' => $before, 'new' => $after],
                'branch' => ['new' => $branch->name],
            ];

            if ($note) {
                $changes['note'] = ['new' => $note];
            }

            $description = match ($mode) {
                self::STOCK_ADD => 'Menambah stok '.$delta.' untuk '.$label.' di '.$branch->name.'.',
                self::STOCK_REDUCE => 'Mengurangi stok '.abs($delta).' untuk '.$label.' di '.$branch->name.'.',
                default => 'Mengoreksi stok '.$label.' di '.$branch->name.' dari '.$before.' menjadi '.$after.'.',
            };

            if ($note) {
                $description .= ' Catatan: '.$note;
            }

            $this->activityLogger->log(
                match ($mode) {
                    self::STOCK_ADD => ActivityLog::ACTION_STOCK_ADDED,
                    self::STOCK_REDUCE => ActivityLog::ACTION_STOCK_REDUCED,
                    default => ActivityLog::ACTION_STOCK_ADJUSTED,
                },
                $variant,
                $label,
                $description,
                $changes,
                $branchId,
            );

            return $stock->refresh();
        });
    }

    private function lockOrder(User $user, Order $order): Order
    {
        $this->ensureStaffBranch($user);

        if ((int) $order->branch_id !== (int) $user->branch_id) {
            throw ValidationException::withMessages([
                'order' => 'Pesanan ini bukan milik cabang kamu.',
            ]);
        }

        return Order::query()
            ->whereKey($order->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureItemBelongsToOrder(Order $order, OrderItem $item): void
    {
        if ((int) $item->order_id !== (int) $order->id) {
            throw ValidationException::withMessages([
                'item' => 'Item tidak ditemukan pada pesanan ini.',
            ]);
        }
    }

    private function ensureCanAddItems(Order $order): void
    {
        if (! $order->canAddItems()) {
            throw ValidationException::withMessages([
                'items' => 'Pesanan yang sudah selesai atau batal tidak bisa ditambah item.',
            ]);
        }
    }

    private function ensureOrderIsEditable(Order $order): void
    {
        if (! $order->isEditable()) {
            throw ValidationException::withMessages([
                'items' => $order->hasPaidPayment()
                    ? 'Item yang sudah dibayar tidak bisa diubah. Tambahkan item baru sebagai tambahan.'
                    : 'Pesanan yang sudah diproses tidak bisa diubah.',
            ]);
        }
    }

    private function takeStock(Order $order, User $user, int $variantId, int $quantity, string $note): void
    {
        $stock = BranchStock::query()
            ->where('branch_id', $order->branch_id)
            ->where('menu_variant_id', $variantId)
            ->lockForUpdate()
            ->first();

        $available = (int) ($stock?->quantity ?? 0);

        if ($available < $quantity) {
            $variant = MenuVariant::query()->with('menu')->find($variantId);
            $label = $variant
                ? $variant->menu->name.' — '.$variant->displayName()
                : 'Menu #'.$variantId;

            throw ValidationException::withMessages([
                'stock' => 'Stok tidak cukup untuk '.$label.' (butuh '.$quantity.', stok '.$available.'). Tambah stok terlebih dahulu.',
            ]);
        }

        $stock->decrement('quantity', $quantity);

        StockMovement::create([
            'branch_id' => $order->branch_id,
            'menu_variant_id' => $variantId,
            'user_id' => $user->id,
            'movement_type' => 'out',
            'quantity' => $quantity,
            'note' => $note,
            'occurred_at' => now(),
        ]);
    }

    private function returnStock(Order $order, User $user, int $variantId, int $quantity, string $note): void
    {
        $stock = BranchStock::query()
            ->where('branch_id', $order->branch_id)
            ->where('menu_variant_id', $variantId)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            $stock = BranchStock::create([
                'branch_id' => $order->branch_id,
                'menu_variant_id' => $variantId,
                'quantity' => 0,
            ]);
        }

        $stock->increment('quantity', $quantity);

        StockMovement::create([
            'branch_id' => $order->branch_id,
            'menu_variant_id' => $variantId,
            'user_id' => $user->id,
            'movement_type' => 'in',
            'quantity' => $quantity,
            'note' => $note,
            'occurred_at' => now(),
        ]);
    }

    private function ensureStaffBranch(User $user): void
    {
        if (! $user->branch_id) {
            throw ValidationException::withMessages([
                'branch' => 'Akun staff belum terhubung ke cabang.',
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

    private function nextQueueNumber(int $branchId, string $businessDate): int
    {
        DB::table('order_sequences')->insertOrIgnore([
            'branch_id' => $branchId,
            'business_date' => $businessDate,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('order_sequences')
            ->where('branch_id', $branchId)
            ->where('business_date', $businessDate)
            ->lockForUpdate()
            ->first();

        $next = ((int) $sequence->last_number) + 1;

        DB::table('order_sequences')
            ->where('id', $sequence->id)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }

    private function generateOrderCode(int $branchId, string $businessDate, int $queueNumber): string
    {
        return 'WJT-'.$branchId.'-'.str_replace('-', '', $businessDate).'-'.str_pad((string) $queueNumber, 3, '0', STR_PAD_LEFT);
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
