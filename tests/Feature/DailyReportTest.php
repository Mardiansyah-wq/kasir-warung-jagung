<?php

namespace Tests\Feature;

use App\Exports\DailyReportExport;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\DailyReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use ZipArchive;

class DailyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_daily_report_with_totals(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $first = $this->makeOrder($branch, $staff, ['total_amount' => 25000, 'queue_number' => 1]);
        $this->makePayment($first, 25000, 'cash');

        $second = $this->makeOrder($branch, $staff, ['total_amount' => 15000, 'queue_number' => 2]);
        $this->makePayment($second, 15000, 'qris');

        $this->actingAs($owner)
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Harian')
            ->assertSee('Uang Masuk')
            ->assertSee('Rp 40.000')
            ->assertSee('2 transaksi dibayar')
            ->assertSee($branch->name)
            ->assertSee('Tunai')
            ->assertSee('QRIS');
    }

    public function test_report_can_be_filtered_by_branch(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$firstBranch, $firstStaff] = $this->makeBranchStaff('Pusat');
        [$secondBranch, $secondStaff] = $this->makeBranchStaff('Cabang Dua');

        $first = $this->makeOrder($firstBranch, $firstStaff, ['total_amount' => 20000, 'queue_number' => 1]);
        $this->makePayment($first, 20000, 'cash');

        $second = $this->makeOrder($secondBranch, $secondStaff, [
            'total_amount' => 50000,
            'queue_number' => 1,
            'customer_name' => 'Pelanggan Cabang Dua',
        ]);
        $this->makePayment($second, 50000, 'cash');

        $this->actingAs($owner)
            ->get(route('owner.reports.index', ['branch_id' => $firstBranch->id]))
            ->assertOk()
            ->assertSee('Rp 20.000')
            ->assertDontSee('Rp 50.000')
            ->assertDontSee('Pelanggan Cabang Dua');
    }

    public function test_cancelled_orders_are_not_counted_as_sales(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');

        $completed = $this->makeOrder($branch, $staff, ['total_amount' => 10000, 'queue_number' => 1]);
        $this->makePayment($completed, 10000, 'cash');

        $this->makeOrder($branch, $staff, [
            'total_amount' => 99999,
            'queue_number' => 2,
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertSee('Rp 10.000');

        $report = app(DailyReportService::class)->build('2026-11-11');

        $this->assertSame(10000.0, $report['summary']['gross_sales']);
        $this->assertSame(1, $report['summary']['orders_cancelled']);
    }

    public function test_report_can_be_downloaded_as_pdf(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');
        $order = $this->makeOrder($branch, $staff, ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash');

        $response = $this->actingAs($owner)
            ->get(route('owner.reports.pdf', ['date' => '2026-11-11', 'branch_id' => $branch->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'laporan-harian-2026-11-11-pusat.pdf',
            (string) $response->headers->get('content-disposition'),
        );
    }

    public function test_report_can_be_downloaded_as_excel(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        $owner = User::factory()->owner()->create();
        [$branch, $staff] = $this->makeBranchStaff('Pusat');
        $order = $this->makeOrder($branch, $staff, ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash');

        $response = $this->actingAs($owner)
            ->get(route('owner.reports.excel', ['date' => '2026-11-11', 'branch_id' => $branch->id]));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
        $this->assertStringContainsString(
            'laporan-harian-2026-11-11-pusat.xlsx',
            (string) $response->headers->get('content-disposition'),
        );
    }

    public function test_excel_export_contains_all_sheets(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 12:00:00'));

        [$branch, $staff] = $this->makeBranchStaff('Pusat', false);
        $order = $this->makeOrder($branch, $staff, ['total_amount' => 12000]);
        $this->makePayment($order, 12000, 'cash');

        $report = app(DailyReportService::class)->build('2026-11-11');

        $path = tempnam(sys_get_temp_dir(), 'daily-report-test-');

        (new DailyReportExport($report))->writeTo($path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true, 'File Excel tidak dapat dibuka.');
        $workbook = (string) $zip->getFromName('xl/workbook.xml');
        $zip->close();
        unlink($path);

        $this->assertStringContainsString('Ringkasan', $workbook);
        $this->assertStringContainsString('Rincian Pesanan', $workbook);
        $this->assertStringContainsString('Pembayaran', $workbook);
        $this->assertStringContainsString('Produk Terjual', $workbook);
    }

    public function test_invalid_date_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.reports.index', ['date' => 'bukan-tanggal']))
            ->assertSessionHasErrors('date');
    }

    public function test_staff_cannot_access_daily_report(): void
    {
        [, $staff] = $this->makeBranchStaff('Pusat');

        $this->actingAs($staff)
            ->get(route('owner.reports.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('owner.reports.pdf'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('owner.reports.excel'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Branch, 1: User}
     */
    private function makeBranchStaff(string $branchName, bool $activeStaff = true): array
    {
        $branch = Branch::create(['name' => $branchName, 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create(['is_active' => $activeStaff]);

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
            'business_date' => '2026-11-11',
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

    private function makePayment(Order $order, float $amount, string $method): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'received_by' => $order->created_by,
            'payment_method' => $method,
            'amount' => $amount,
            'amount_tendered' => $amount,
            'change_amount' => 0,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }
}
