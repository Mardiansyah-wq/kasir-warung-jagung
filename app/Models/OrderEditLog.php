<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'user_id',
    'action_type',
    'before_data',
    'after_data',
    'reason',
])]
class OrderEditLog extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action_type) {
            'created' => 'Pesanan dibuat',
            'item_added' => 'Item ditambah',
            'item_updated' => 'Item diubah',
            'item_removed' => 'Item dihapus',
            'discount_changed' => 'Diskon diubah',
            'paid' => 'Pembayaran masuk',
            'status_changed' => 'Status diubah',
            'cancelled' => 'Pesanan dibatalkan',
            default => 'Perubahan',
        };
    }

    public function actionTone(): string
    {
        if ($this->action_type === 'cancelled') {
            return 'status-cancelled';
        }

        if ($this->action_type === 'paid') {
            return 'status-paid';
        }

        $status = (string) ($this->after_data['status'] ?? '');

        if ($this->action_type === 'status_changed' && in_array($status, Order::STATUSES, true)) {
            return 'status-'.$status;
        }

        return 'status-draft';
    }

    /**
     * Ringkasan perubahan yang bisa dibaca owner.
     *
     * @param  array<int|string, string>  $variantLabels
     */
    public function changeSummary(array $variantLabels = []): string
    {
        $before = is_array($this->before_data) ? $this->before_data : [];
        $after = is_array($this->after_data) ? $this->after_data : [];
        $variantId = $after['menu_variant_id'] ?? $before['menu_variant_id'] ?? null;
        $item = is_scalar($variantId) ? ($variantLabels[$variantId] ?? 'Item') : 'Item';
        $money = static fn (mixed $amount): string => 'Rp '.number_format((float) $amount, 0, ',', '.');

        return match ($this->action_type) {
            'created' => 'Antrian '.$this->queueLabel((int) ($after['queue_number'] ?? 0))
                .' · '.(string) ($after['customer_name'] ?? 'Pelanggan')
                .' · '.$this->orderTypeLabel((string) ($after['order_type'] ?? '')),
            'item_added' => $item.' · '.(int) ($after['quantity'] ?? 0).'x',
            'item_updated' => $item.' · '.(int) ($before['quantity'] ?? 0).'x → '.(int) ($after['quantity'] ?? 0).'x',
            'item_removed' => $item.' · '.(int) ($before['quantity'] ?? 0).'x dihapus',
            'discount_changed' => $this->discountSummary($before, $after, $money),
            'paid' => Payment::methodLabelFor((string) ($after['payment_method'] ?? '')).' '.$money($after['amount'] ?? 0),
            'status_changed' => $this->statusSummary($before, $after),
            'cancelled' => 'Status menjadi '.Order::statusLabelFor(Order::STATUS_CANCELLED),
            default => 'Perubahan dicatat',
        };
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  callable(mixed): string  $money
     */
    private function discountSummary(array $before, array $after, callable $money): string
    {
        $summary = 'Diskon '.$money($before['discount_amount'] ?? 0).' → '.$money($after['discount_amount'] ?? 0);
        $reason = trim((string) ($this->reason ?? ''));

        return $reason === '' ? $summary.' · Tanpa alasan' : $summary.' · '.$reason;
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function statusSummary(array $before, array $after): string
    {
        $summary = Order::statusLabelFor((string) ($before['status'] ?? Order::STATUS_OPEN))
            .' → '.Order::statusLabelFor((string) ($after['status'] ?? Order::STATUS_OPEN));
        $reason = trim((string) ($after['reason'] ?? ''));

        return $reason === '' ? $summary : $summary.' · '.$reason;
    }

    private function queueLabel(int $number): string
    {
        return '#'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function orderTypeLabel(string $type): string
    {
        return $type === Order::ORDER_TYPE_TAKEAWAY ? 'Bawa Pulang' : 'Dine-in';
    }
}
