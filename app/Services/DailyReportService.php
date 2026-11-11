<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DailyReportService
{
    /**
     * @var array<string, string>
     */
    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        'other' => 'Lainnya',
    ];

    /**
     * Build the report payload for a single business date and optional branch.
     *
     * Orders are counted by their business date, while revenue is counted from
     * payments actually received on that date, so the two numbers may differ.
     *
     * @return array{
     *     date: string,
     *     date_label: string,
     *     branch: Branch|null,
     *     branch_label: string,
     *     summary: array<string, int|float>,
     *     methods: Collection<int, array{method: string, label: string, count: int, amount: float}>,
     *     orders: Collection<int, Order>,
     *     products: Collection<int, array{name: string, variant: string, quantity: int, revenue: float}>
     * }
     */
    public function build(string $date, ?int $branchId = null): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $branch = $branchId ? Branch::query()->find($branchId) : null;

        $orders = Order::query()
            ->whereDate('business_date', $day->toDateString())
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->with([
                'branch',
                'creator',
                'payments' => fn ($query) => $query->where('status', Payment::STATUS_PAID),
            ])
            ->withCount('items')
            ->orderBy('branch_id')
            ->orderBy('queue_number')
            ->get();

        $payments = Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereDate('paid_at', $day->toDateString())
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('order', fn ($query) => $query->where('branch_id', $branchId));
            })
            ->get();

        $methods = collect(self::PAYMENT_METHODS)
            ->map(function (string $label, string $method) use ($payments): array {
                $group = $payments->where('payment_method', $method);

                return [
                    'method' => $method,
                    'label' => $label,
                    'count' => $group->count(),
                    'amount' => (float) $group->sum('amount'),
                ];
            })
            ->values();

        $revenue = (float) $payments->sum('amount');
        $paidOrders = $payments->pluck('order_id')->unique()->count();

        $activeOrders = $orders->filter(fn (Order $order) => $order->isActive());
        $completedOrders = $orders->filter(fn (Order $order) => $order->isCompleted());
        $cancelledOrders = $orders->filter(fn (Order $order) => $order->isCancelled());
        $billableOrders = $orders->reject(fn (Order $order) => $order->isCancelled());

        return [
            'date' => $day->toDateString(),
            'date_label' => $day->locale('id')->isoFormat('dddd, D MMMM Y'),
            'branch' => $branch,
            'branch_label' => $branch?->name ?? 'Semua Cabang',
            'summary' => [
                'orders_total' => $orders->count(),
                'orders_completed' => $completedOrders->count(),
                'orders_active' => $activeOrders->count(),
                'orders_cancelled' => $cancelledOrders->count(),
                'gross_sales' => (float) $billableOrders->sum(fn (Order $order) => (float) $order->total_amount),
                'discounts' => (float) $billableOrders->sum(fn (Order $order) => (float) $order->discount_amount),
                'revenue' => $revenue,
                'paid_orders' => $paidOrders,
                'average' => $paidOrders > 0 ? $revenue / $paidOrders : 0.0,
                'outstanding' => (float) $billableOrders->sum(fn (Order $order) => $order->balanceDue()),
            ],
            'methods' => $methods,
            'orders' => $orders,
            'products' => $this->products($day->toDateString(), $branchId),
        ];
    }

    /**
     * @return Collection<int, array{name: string, variant: string, quantity: int, revenue: float}>
     */
    private function products(string $date, ?int $branchId): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.business_date', $date)
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->when($branchId, fn ($query) => $query->where('orders.branch_id', $branchId))
            ->groupBy('order_items.product_name_snapshot', 'order_items.variant_name_snapshot')
            ->orderByDesc('revenue')
            ->selectRaw(
                'order_items.product_name_snapshot as name, '
                .'order_items.variant_name_snapshot as variant, '
                .'SUM(order_items.quantity) as quantity, '
                .'SUM(order_items.line_total) as revenue',
            )
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => (string) $item->getAttribute('name'),
                'variant' => (string) $item->getAttribute('variant'),
                'quantity' => (int) $item->getAttribute('quantity'),
                'revenue' => (float) $item->getAttribute('revenue'),
            ]);
    }
}
