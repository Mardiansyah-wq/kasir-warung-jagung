<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
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

        $payments = $this->paymentsBetween($day, $day->endOfDay(), $branchId);

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
            'methods' => $this->methodsFor($payments),
            'orders' => $orders,
            'products' => $this->productsBetween($day, $day->endOfDay(), $branchId),
        ];
    }

    /**
     * Build the weekly report payload (Monday to Sunday) for the week that
     * contains the given date.
     *
     * @return array<string, mixed>
     */
    public function buildWeekly(string $date, ?int $branchId = null): array
    {
        $anchor = CarbonImmutable::parse($date)->startOfDay();
        $start = $anchor->startOfWeek(CarbonInterface::MONDAY);
        $end = $start->endOfWeek(CarbonInterface::SUNDAY);

        $branch = $branchId ? Branch::query()->find($branchId) : null;

        $orders = Order::query()
            ->whereBetween('business_date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->withSum(
                ['payments as paid_amount' => fn ($query) => $query->where('status', Payment::STATUS_PAID)],
                'amount',
            )
            ->get();

        $payments = $this->paymentsBetween($start, $end, $branchId);

        $ordersByDay = $orders->groupBy(
            fn (Order $order) => $order->business_date?->toDateString() ?? '',
        );

        $paymentsByDay = $payments->groupBy(
            fn (Payment $payment) => $payment->paid_at?->toDateString() ?? '',
        );

        $days = [];

        for ($cursor = $start; $cursor->lessThanOrEqualTo($end); $cursor = $cursor->addDay()) {
            $key = $cursor->toDateString();
            $dayOrders = $ordersByDay->get($key, collect());
            $dayPayments = $paymentsByDay->get($key, collect());
            $billable = $dayOrders->reject(fn (Order $order) => $order->isCancelled());

            $days[] = [
                'date' => $key,
                'day_label' => $cursor->locale('id')->isoFormat('ddd'),
                'date_label' => $cursor->locale('id')->isoFormat('D MMM'),
                'orders_total' => $dayOrders->count(),
                'orders_cancelled' => $dayOrders->filter(fn (Order $order) => $order->isCancelled())->count(),
                'paid_orders' => $dayPayments->pluck('order_id')->unique()->count(),
                'revenue' => (float) $dayPayments->sum('amount'),
                'gross_sales' => (float) $billable->sum(fn (Order $order) => (float) $order->total_amount),
                'is_today' => $cursor->isSameDay(today()),
            ];
        }

        $billableOrders = $orders->reject(fn (Order $order) => $order->isCancelled());
        $revenue = (float) $payments->sum('amount');
        $paidOrders = $payments->pluck('order_id')->unique()->count();
        $bestDay = collect($days)->sortByDesc('revenue')->first();

        $previousStart = $start->subWeek();
        $previousEnd = $end->subWeek();
        $previousPayments = $this->paymentsBetween($previousStart, $previousEnd, $branchId);
        $previousRevenue = (float) $previousPayments->sum('amount');
        $previousOrders = Order::query()
            ->whereBetween('business_date', [$previousStart->toDateString(), $previousEnd->toDateString()])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->count();

        return [
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'week_label' => $this->weekLabel($start, $end),
            'week_number' => (int) $start->isoWeek(),
            'branch' => $branch,
            'branch_label' => $branch?->name ?? 'Semua Cabang',
            'days' => $days,
            'summary' => [
                'orders_total' => $orders->count(),
                'orders_completed' => $orders->filter(fn (Order $order) => $order->isCompleted())->count(),
                'orders_active' => $orders->filter(fn (Order $order) => $order->isActive())->count(),
                'orders_cancelled' => $orders->filter(fn (Order $order) => $order->isCancelled())->count(),
                'gross_sales' => (float) $billableOrders->sum(fn (Order $order) => (float) $order->total_amount),
                'discounts' => (float) $billableOrders->sum(fn (Order $order) => (float) $order->discount_amount),
                'revenue' => $revenue,
                'paid_orders' => $paidOrders,
                'average_per_day' => $revenue / 7,
                'outstanding' => (float) $billableOrders->sum(fn (Order $order) => max(
                    0,
                    (float) $order->total_amount - (float) ($order->paid_amount ?? 0),
                )),
                'best_day_label' => $bestDay['date_label'] ?? '—',
                'best_day_revenue' => (float) ($bestDay['revenue'] ?? 0),
            ],
            'comparison' => [
                'previous_revenue' => $previousRevenue,
                'revenue_delta' => $revenue - $previousRevenue,
                'revenue_delta_percent' => $previousRevenue > 0
                    ? (($revenue - $previousRevenue) / $previousRevenue) * 100
                    : null,
                'previous_orders' => $previousOrders,
                'orders_delta' => $orders->count() - $previousOrders,
            ],
            'methods' => $this->methodsFor($payments),
            'products' => $this->productsBetween($start, $end, $branchId),
        ];
    }

    /**
     * @return Collection<int, Payment>
     */
    private function paymentsBetween(
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $branchId,
    ): Collection {
        return Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<=', $end)
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('order', fn ($query) => $query->where('branch_id', $branchId));
            })
            ->get();
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @return Collection<int, array{method: string, label: string, count: int, amount: float}>
     */
    private function methodsFor(Collection $payments): Collection
    {
        return collect(self::PAYMENT_METHODS)
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
    }

    /**
     * @return Collection<int, array{name: string, variant: string, quantity: int, revenue: float}>
     */
    private function productsBetween(
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $branchId,
    ): Collection {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.business_date', [$start->toDateString(), $end->toDateString()])
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

    private function weekLabel(CarbonImmutable $start, CarbonImmutable $end): string
    {
        if ($start->month === $end->month && $start->year === $end->year) {
            return $start->locale('id')->isoFormat('D')
                .' - '.$end->locale('id')->isoFormat('D MMMM Y');
        }

        return $start->locale('id')->isoFormat('D MMM Y')
            .' - '.$end->locale('id')->isoFormat('D MMM Y');
    }
}
