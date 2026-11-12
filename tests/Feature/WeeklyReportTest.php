<?php

namespace Tests\Feature;

use App\Exports\WeeklyReportExport;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\DailyReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use ZipArchive;

class WeeklyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_weekly_report(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $first = $this->makeOrder($branch, $staff, '2026-11-10', ['total_amount' => 20000]);
        $this->makePayment($first, 20000, 'cash', '2026-11-10 10:00:00');

        $second = $this->makeOrder($branch, $staff, '2026-11-12', ['total_amount' => 30000]);
        $this->makePayment($second, 30000, 'qris', '2026-11-12 11:00:00');

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly'))
            ->assertOk()
            ->assertSee('Laporan Mingguan')
            ->assertSee('9 - 15 November 2026')
            ->assertSee('Rp 50.000')
            ->assertSee('2 transaksi dibayar')
            ->assertSee('Rekap Harian')
            ->assertSee($branch->name)
            ->assertSee('Tunai')
            ->assertSee('QRIS');
    }

    public function test_week_range_starts_on_monday_and_ends_on_sunday(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        $owner = User::factory()->owner()->create();

        $report = app(DailyReportService::class)->buildWeekly('2026-11-11');

        $this->assertSame('2026-11-09', $report['week_start']);
        $this->assertSame('2026-11-15', $report['week_end']);
        $this->assertSame('9 - 15 November 2026', $report['week_label']);
        $this->assertCount(7, $report['days']);
        $this->assertSame('Sen', $report['days'][0]['day_label']);
        $this->assertSame('Min', $report['days'][6]['day_label']);

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly', ['week' => '2026-11-11']))
            ->assertOk()
            ->assertSee('9 Nov')
            ->assertSee('15 Nov');
    }

    public function test_weekly_report_can_be_filtered_by_branch(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$firstBranch, $firstStaff] = $this->makeBranchStaff('Pusat');
        [$secondBranch, $secondStaff] = $this->makeBranchStaff('Cabang Dua');

        $first = $this->makeOrder($firstBranch, $firstStaff, '2026-11-10', ['total_amount' => 20000]);
        $this->makePayment($first, 20000, 'cash', '2026-11-10 10:00:00');

        $second = $this->makeOrder($secondBranch, $secondStaff, '2026-11-10', [
            'total_amount' => 50000,
            'customer_name' => 'Pelanggan Cabang Dua',
        ]);
        $this->makePayment($second, 50000, 'cash', '2026-11-10 10:00:00');

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly', ['branch_id' => $firstBranch->id]))
            ->assertOk()
            ->assertSee('Rp 20.000')
            ->assertDontSee('Rp 50.000');
    }

    public function test_cancelled_orders_are_not_counted_as_weekly_sales(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $completed = $this->makeOrder($branch, $staff, '2026-11-10', ['total_amount' => 10000]);
        $this->makePayment($completed, 10000, 'cash', '2026-11-10 10:00:00');

        $this->makeOrder($branch, $staff, '2026-11-11', [
            'total_amount' => 99999,
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $report = app(DailyReportService::class)->buildWeekly('2026-11-12');

        $this->assertSame(10000.0, $report['summary']['gross_sales']);
        $this->assertSame(1, $report['summary']['orders_cancelled']);
        $this->assertSame(1, $report['days'][2]['orders_cancelled']);

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly'))
            ->assertOk()
            ->assertSee('Rp 10.000');
    }

    public function test_revenue_follows_the_payment_date_not_the_order_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $order = $this->makeOrder($branch, $staff, '2026-11-09', ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash', '2026-11-12 09:00:00');

        $report = app(DailyReportService::class)->buildWeekly('2026-11-12');

        $this->assertSame(12000.0, $report['summary']['revenue']);
        $this->assertSame(0.0, $report['days'][0]['revenue']);
        $this->assertSame(12000.0, $report['days'][3]['revenue']);
    }

    public function test_weekly_report_compares_with_the_previous_week(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $lastWeek = $this->makeOrder($branch, $staff, '2026-11-04', ['total_amount' => 8000]);
        $this->makePayment($lastWeek, 8000, 'cash', '2026-11-04 10:00:00');

        $thisWeek = $this->makeOrder($branch, $staff, '2026-11-10', ['total_amount' => 12000]);
        $this->makePayment($thisWeek, 12000, 'cash', '2026-11-10 10:00:00');

        $report = app(DailyReportService::class)->buildWeekly('2026-11-12');

        $this->assertSame(12000.0, $report['summary']['revenue']);
        $this->assertSame(8000.0, $report['comparison']['previous_revenue']);
        $this->assertSame(4000.0, $report['comparison']['revenue_delta']);
        $this->assertSame(50.0, $report['comparison']['revenue_delta_percent']);
        $this->assertSame(1, $report['comparison']['previous_orders']);
        $this->assertSame(0, $report['comparison']['orders_delta']);
    }

    public function test_weekly_report_can_be_downloaded_as_pdf(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');
        $order = $this->makeOrder($branch, $staff, '2026-11-10', ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash', '2026-11-10 10:00:00');

        $response = $this->actingAs($owner)
            ->get(route('owner.reports.weekly.pdf', ['week' => '2026-11-12', 'branch_id' => $branch->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'laporan-mingguan-2026-11-09-sd-2026-11-15-pusat.pdf',
            (string) $response->headers->get('content-disposition'),
        );
    }

    public function test_weekly_report_can_be_downloaded_as_excel(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');
        $order = $this->makeOrder($branch, $staff, '2026-11-10', ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash', '2026-11-10 10:00:00');

        $response = $this->actingAs($owner)
            ->get(route('owner.reports.weekly.excel', ['week' => '2026-11-12', 'branch_id' => $branch->id]));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
        $this->assertStringContainsString(
            'laporan-mingguan-2026-11-09-sd-2026-11-15-pusat.xlsx',
            (string) $response->headers->get('content-disposition'),
        );
    }

    public function test_weekly_excel_export_contains_all_sheets(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        [$branch, $staff] = $this->makeBranchStaff('Pusat');
        $order = $this->makeOrder($branch, $staff, '2026-11-10', ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash', '2026-11-10 10:00:00');

        $report = app(DailyReportService::class)->buildWeekly('2026-11-12');
        $path = tempnam(sys_get_temp_dir(), 'weekly-report-test-');

        (new WeeklyReportExport($report))->writeTo($path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true, 'File Excel tidak dapat dibuka.');
        $workbook = (string) $zip->getFromName('xl/workbook.xml');
        $zip->close();
        unlink($path);

        $this->assertStringContainsString('Ringkasan Mingguan', $workbook);
        $this->assertStringContainsString('Rekap Harian', $workbook);
        $this->assertStringContainsString('Pembayaran', $workbook);
        $this->assertStringContainsString('Produk Terjual', $workbook);
    }

    public function test_invalid_week_date_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly', ['week' => 'bukan-tanggal']))
            ->assertSessionHasErrors('week');
    }

    public function test_staff_cannot_access_weekly_report(): void
    {
        [, $staff] = $this->makeBranchStaff('Pusat');

        $this->actingAs($staff)
            ->get(route('owner.reports.weekly'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('owner.reports.weekly.pdf'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('owner.reports.weekly.excel'))
            ->assertForbidden();
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
    private function makeOrder(Branch $branch, User $staff, string $date, array $attributes = []): Order
    {
        static $counter = 0;

        $counter++;

        return Order::create(array_merge([
            'order_code' => 'WEEK-'.$branch->id.'-'.$counter,
            'queue_number' => $counter,
            'business_date' => $date,
            'customer_name' => 'Pelanggan '.$counter,
            'order_type' => Order::ORDER_TYPE_DINE_IN,
            'branch_id' => $branch->id,
            'created_by' => $staff->id,
            'status' => Order::STATUS_COMPLETED,
            'subtotal' => 12000,
            'discount_amount' => 0,
            'total_amount' => 12000,
            'completed_at' => now(),
        ], $attributes));
    }

    private function makePayment(Order $order, float $amount, string $method, string $paidAt): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'received_by' => $order->created_by,
            'payment_method' => $method,
            'amount' => $amount,
            'amount_tendered' => $amount,
            'change_amount' => 0,
            'status' => Payment::STATUS_PAID,
            'paid_at' => Carbon::parse($paidAt),
        ]);
    }
}
