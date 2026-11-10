<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_code',
    'queue_number',
    'business_date',
    'customer_name',
    'order_type',
    'branch_id',
    'created_by',
    'status',
    'subtotal',
    'discount_amount',
    'total_amount',
    'notes',
    'paid_at',
    'completed_at',
    'cancelled_at',
])]
class Order extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PROCESSING,
        self::STATUS_READY,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const ACTIVE_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PROCESSING,
        self::STATUS_READY,
    ];

    public const ORDER_TYPE_DINE_IN = 'dine_in';

    public const ORDER_TYPE_TAKEAWAY = 'takeaway';

    public const ORDER_TYPES = [
        self::ORDER_TYPE_DINE_IN,
        self::ORDER_TYPE_TAKEAWAY,
    ];

    /** @var list<string> */
    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'queue_number' => 'int',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function editLogs(): HasMany
    {
        return $this->hasMany(OrderEditLog::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function canAddItems(): bool
    {
        return $this->isActive();
    }

    public function paidAmount(): float
    {
        if (array_key_exists('paid_amount', $this->attributes)) {
            return (float) $this->attributes['paid_amount'];
        }

        if ($this->relationLoaded('payments')) {
            return (float) $this->payments
                ->where('status', Payment::STATUS_PAID)
                ->sum('amount');
        }

        return (float) $this->payments()
            ->where('status', Payment::STATUS_PAID)
            ->sum('amount');
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total_amount - $this->paidAmount());
    }

    public function isFullyPaid(): bool
    {
        return $this->balanceDue() <= 0.0;
    }

    public function hasPaidPayment(): bool
    {
        return $this->paidAmount() > 0.0;
    }

    public function isEditable(): bool
    {
        return $this->isOpen() && ! $this->hasPaidPayment();
    }

    public function canCancel(): bool
    {
        return $this->isActive() && ! $this->hasPaidPayment();
    }

    public function canComplete(): bool
    {
        return $this->isActive() && $this->isFullyPaid();
    }

    public function paymentStatus(): string
    {
        $paid = $this->paidAmount();

        if ($paid <= 0.0) {
            return 'unpaid';
        }

        return $paid + 0.001 < (float) $this->total_amount ? 'partial' : 'paid';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->paymentStatus()) {
            'partial' => 'Sebagian',
            'paid' => 'Lunas',
            default => 'Belum bayar',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_READY => 'Siap',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Batal',
            default => 'Aktif',
        };
    }

    public function orderTypeLabel(): string
    {
        return $this->order_type === self::ORDER_TYPE_TAKEAWAY ? 'Bawa Pulang' : 'Dine-in';
    }

    public function queueLabel(): string
    {
        return '#'.str_pad((string) ($this->queue_number ?? 0), 3, '0', STR_PAD_LEFT);
    }

    public function paidPayment(): ?Payment
    {
        return $this->payments
            ->where('status', Payment::STATUS_PAID)
            ->sortByDesc('paid_at')
            ->first();
    }
}
