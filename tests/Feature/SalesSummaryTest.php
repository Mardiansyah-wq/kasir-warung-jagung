<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\DailyReportService;
use App\Support\ReportRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SalesSummaryTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, MenuVariant> */
    private array $variants = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-16 12:00:00'));
    }

    public function test_range_defaults_to_the_last_seven_days(): void
    {
        $range = ReportRange::fromRequest(Request::create('/owner/dashboard'));

        $this->assertSame(ReportRange::WEEK, $range->preset);
        $this->assertSame('2026-11-10', $range->start->toDateString());
        $this->assertSame('2026-11-16', $range->end->toDateString());
        $this->assertSame('2026-11-03', $range->previousStart->toDateString());
        $this->assertSame('2026-11-09', $range->previousEnd->toDateString());
        $this->assertSame(7, $range->days());
        $this->assertSame('10 – 16 November 2026', $range->label());
        $this->assertSame('vs 7 hari lalu', $range->previousLabel());
    }

    public function test_unknown_range_falls_back_to_the_last_seven_days(): void
    {
        $range = ReportRange::make('kemarin');

        $this->assertSame(ReportRange::WEEK, $range->preset);
        $this->assertSame(7, $range->days());
    }

    public function test_month_range_runs_from_the_first_day_and_compares_the_same_span_last_month(): void
    {
        $range = ReportRange::make(ReportRange::MONTH);

        $this->assertSame('2026-11-01', $range->start->toDateString());
        $this->assertSame('2026-11-16', $range->end->toDateString());
        $this->assertSame('2026-10-01', $range->previousStart->toDateString());
        $this->assertSame('2026-10-16', $range->previousEnd->toDateString());
        $this->assertSame('1 – 16 November 2026', $range->label());
        $this->assertSame('vs bulan lalu', $range->previousLabel());
    }

    public function test_month_range_never_compares_past_the_previous_month_end(): void
    {
        $this->travelTo(Carbon::parse('2026-03-31 12:00:00'));

        $range = ReportRange::make(ReportRange::MONTH);

        $this->assertSame('2026-03-01', $range->start->toDateString());
        $this->assertSame('2026-02-01', $range->previousStart->toDateString());
        $this->assertSame('2026-02-28', $range->previousEnd->toDateString());
    }

    public function test_revenue_only_counts_paid_payments_inside_the_range(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $inRange = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-12']);
        $this->makePayment($inRange, 30000, 'cash', '2026-11-12 10:00:00');

        $outside = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-09']);
        $this->makePayment($outside, 99999, 'cash', '2026-11-09 10:00:00');

        $pending = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-13']);
        $this->makePayment($pending, 50000, 'qris', '2026-11-13 10:00:00', Payment::STATUS_PENDING);

        $report = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK));

        $this->assertSame(30000.0, $report['summary']['revenue']);
        $this->assertSame(1, $report['summary']['payments_count']);
        $this->assertSame(1, $report['summary']['paid_orders']);
    }

    public function test_cancelled_orders_are_left_out_of_transactions_and_items_sold(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $first = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-12']);
        $this->addItem($first, 'Kopi Susu Senja', 'Reguler', 2, 20000);

        $second = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-13']);
        $this->addItem($second, 'Roti Bakar', 'Cokelat', 3, 30000);

        $cancelled = $this->makeOrder($branch, $staff, [
            'business_date' => '2026-11-14',
            'status' => Order::STATUS_CANCELLED,
        ]);
        $this->addItem($cancelled, 'Es Cokelat', 'Reguler', 10, 100000);

        $report = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK));

        $this->assertSame(2, $report['summary']['orders']);
        $this->assertSame(5, $report['summary']['items_sold']);
        $this->assertSame(2, $report['orders_total']);
    }

    public function test_top_products_merge_variants_into_one_menu_row(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $order = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-12']);
        $this->addItem($order, 'Kopi Susu Senja', 'Reguler', 2, 20000);
        $this->addItem($order, 'Kopi Susu Senja', 'Large', 3, 39000);
        $this->addItem($order, 'Roti Bakar', 'Cokelat', 1, 12000);

        $products = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK))['top_products'];

        $this->assertSame('Kopi Susu Senja', $products[0]['name']);
        $this->assertSame(5, $products[0]['quantity']);
        $this->assertSame(59000.0, $products[0]['revenue']);
        $this->assertSame('Roti Bakar', $products[1]['name']);
    }

    public function test_comparison_uses_the_previous_period_of_the_same_length(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $broken = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-05']);
        $this->makePayment($broken, 100000, 'cash', '2026-11-05 10:00:00');

        $recent = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-15']);
        $this->makePayment($recent, 150000, 'cash', '2026-11-15 10:00:00');

        $report = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK));

        $this->assertSame(100000.0, (float) $report['comparison']['previous']['revenue']);
        $this->assertSame(50000.0, (float) $report['comparison']['revenue_delta']);
        $this->assertEqualsWithDelta(50.0, $report['comparison']['revenue_delta_percent'], 0.001);
        $this->assertSame('vs 7 hari lalu', $report['comparison']['label']);
    }

    public function test_empty_previous_period_does_not_produce_a_percentage(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $order = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-15']);
        $this->makePayment($order, 20000, 'cash', '2026-11-15 10:00:00');

        $comparison = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK))['comparison'];

        $this->assertNull($comparison['revenue_delta_percent']);
        $this->assertSame(20000.0, (float) $comparison['revenue_delta']);
    }

    public function test_dashboard_reads_range_and_branch_from_the_query_string(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');
        $order = $this->makeOrder($branch, $staff, [
            'business_date' => '2026-11-02',
            'total_amount' => 40000,
        ]);
        $this->makePayment($order, 40000, 'cash', '2026-11-02 10:00:00');

        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)
            ->get(route('owner.dashboard', ['range' => 'month', 'branch' => $branch->id]))
            ->assertOk();

        $summary = $response->viewData('summary');

        $this->assertSame('month', $summary['range']['preset']);
        $this->assertSame('Pusat', $summary['branch_label']);
        $this->assertSame(40000.0, $summary['summary']['revenue']);
    }

    public function test_dashboard_ignores_an_unknown_branch(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)
            ->get(route('owner.dashboard', ['branch' => 999999]))
            ->assertOk();

        $this->assertNull($response->viewData('branchId'));
        $this->assertSame('Semua Cabang', $response->viewData('summary')['branch_label']);
    }

    public function test_branch_filter_scopes_every_number(): void
    {
        [$firstBranch, $firstStaff] = $this->makeBranchStaff('Pusat');
        [$secondBranch, $secondStaff] = $this->makeBranchStaff('Cabang Dua');

        $first = $this->makeOrder($firstBranch, $firstStaff, [
            'business_date' => '2026-11-12',
            'total_amount' => 25000,
        ]);
        $this->addItem($first, 'Kopi Susu Senja', 'Reguler', 2, 20000);
        $this->makePayment($first, 20000, 'cash', '2026-11-12 10:00:00');
        $this->makePayment($first, 5000, 'qris', '2026-11-12 11:00:00');

        $second = $this->makeOrder($secondBranch, $secondStaff, [
            'business_date' => '2026-11-12',
            'customer_name' => 'Pelanggan Cabang Dua',
            'total_amount' => 84000,
        ]);
        $this->addItem($second, 'Roti Bakar', 'Cokelat', 7, 84000);
        $this->makePayment($second, 84000, 'cash', '2026-11-12 10:00:00');

        $report = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK), $firstBranch->id);

        $this->assertSame('Pusat', $report['branch_label']);
        $this->assertSame(25000.0, $report['summary']['revenue']);
        $this->assertSame(1, $report['summary']['orders']);
        $this->assertSame(2, $report['summary']['items_sold']);
        $this->assertSame(2, $report['summary']['payments_count']);
        $this->assertSame('Kopi Susu Senja', $report['top_products'][0]['name']);
        $this->assertSame(1, $report['recent_orders']->count());
        $this->assertSame(25000.0, $report['recent_orders'][0]['total']);
    }

    public function test_days_payload_has_one_bar_per_day_and_marks_today(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $order = $this->makeOrder($branch, $staff, ['business_date' => '2026-11-14']);
        $this->makePayment($order, 15000, 'cash', '2026-11-14 09:00:00');

        $days = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK))['days'];

        $this->assertCount(7, $days);
        $this->assertSame('2026-11-10', $days[0]['date']);
        $this->assertSame('2026-11-16', $days[6]['date']);
        $this->assertSame(15000.0, $days[4]['revenue']);
        $this->assertTrue($days[6]['is_today']);
        $this->assertFalse($days[5]['is_today']);
        $this->assertSame('16 Nov', $days[6]['label']);
    }

    public function test_recent_orders_show_only_the_latest_four_with_payment_status(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        for ($index = 1; $index <= 5; $index++) {
            $order = $this->makeOrder($branch, $staff, [
                'business_date' => '2026-11-1'.$index,
                'customer_name' => 'Pelanggan '.$index,
                'total_amount' => 20000,
            ]);
            $this->addItem($order, 'Kopi Susu Senja', 'Reguler', 2, 20000);
            $this->makePayment($order, 20000, 'cash', '2026-11-1'.$index.' 10:00:00');
        }

        $report = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK));

        $this->assertSame(5, $report['orders_total']);
        $this->assertCount(4, $report['recent_orders']);

        $latest = $report['recent_orders'][0];

        $this->assertSame('2 Kopi Susu Senja', $latest['items_label']);
        $this->assertSame('Tunai', $latest['payment_label']);
        $this->assertSame('Lunas', $latest['status_label']);
        $this->assertSame('15 November 2026', $latest['date_label']);
    }

    public function test_recent_orders_leave_out_cancelled_orders(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $paid = $this->makeOrder($branch, $staff, [
            'business_date' => '2026-11-12',
            'total_amount' => 15000,
        ]);
        $this->addItem($paid, 'Kopi Susu Senja', 'Reguler', 1, 15000);
        $this->makePayment($paid, 15000, 'cash', '2026-11-12 10:00:00');

        $this->makeOrder($branch, $staff, [
            'business_date' => '2026-11-13',
            'status' => Order::STATUS_CANCELLED,
            'total_amount' => 20000,
        ]);

        $report = $this->service()->buildRange(ReportRange::make(ReportRange::WEEK));

        $this->assertSame(1, $report['orders_total']);
        $this->assertCount(1, $report['recent_orders']);
        $this->assertSame($paid->order_code, $report['recent_orders'][0]['code']);
    }

    public function test_dashboard_shows_favorite_menu_and_latest_transactions(): void
    {
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $order = $this->makeOrder($branch, $staff, [
            'business_date' => '2026-11-12',
            'total_amount' => 25000,
        ]);
        $this->addItem($order, 'Kopi Susu Senja', 'Reguler', 2, 25000);
        $this->makePayment($order, 25000, 'cash', '2026-11-12 10:00:00');

        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Favorit pelanggan')
            ->assertSee('Kopi Susu Senja')
            ->assertSee('2 terjual')
            ->assertSee('Transaksi terbaru')
            ->assertSee($order->order_code)
            ->assertSee('Menampilkan 1 dari 1 transaksi');
    }

    private function service(): DailyReportService
    {
        return app(DailyReportService::class);
    }

    /**
     * @return array{0: Branch, 1: User}
     */
    private function makeBranchStaff(string $branchName): array
    {
        $branch = Branch::create(['name' => $branchName, 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();

        return [$branch, $staff];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeOrder(Branch $branch, User $staff, array $attributes = []): Order
    {
        static $counter = 0;

        $counter++;

        return Order::create(array_merge([
            'order_code' => 'TEST-'.$branch->id.'-'.$counter,
            'queue_number' => $counter,
            'business_date' => '2026-11-16',
            'customer_name' => 'Pelanggan '.$counter,
            'order_type' => Order::ORDER_TYPE_DINE_IN,
            'branch_id' => $branch->id,
            'created_by' => $staff->id,
            'status' => Order::STATUS_COMPLETED,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'completed_at' => now(),
        ], $attributes));
    }

    private function addItem(
        Order $order,
        string $menu,
        string $variant,
        int $quantity,
        float $lineTotal,
        float $unitPrice = 10000,
    ): OrderItem {
        return OrderItem::create([
            'order_id' => $order->id,
            'menu_variant_id' => $this->variantFor($menu, $variant)->id,
            'product_name_snapshot' => $menu,
            'variant_name_snapshot' => $variant,
            'topping_price' => 0,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'is_addon' => false,
            'line_total' => $lineTotal,
        ]);
    }

    private function variantFor(string $menu, string $variant): MenuVariant
    {
        $key = $menu.'|'.$variant;

        if (! isset($this->variants[$key])) {
            $model = Menu::create([
                'name' => $menu,
                'category' => Menu::CATEGORY_MAKANAN,
                'is_active' => true,
            ]);

            $this->variants[$key] = $model->variants()->create([
                'variant_name' => $variant,
                'selling_price' => 10000,
                'is_active' => true,
            ]);
        }

        return $this->variants[$key];
    }

    private function makePayment(
        Order $order,
        float $amount,
        string $method,
        string $paidAt,
        string $status = Payment::STATUS_PAID,
    ): Payment {
        return Payment::create([
            'order_id' => $order->id,
            'received_by' => $order->created_by,
            'payment_method' => $method,
            'amount' => $amount,
            'amount_tendered' => $amount,
            'change_amount' => 0,
            'status' => $status,
            'paid_at' => $status === Payment::STATUS_PAID ? $paidAt : null,
        ]);
    }
}
