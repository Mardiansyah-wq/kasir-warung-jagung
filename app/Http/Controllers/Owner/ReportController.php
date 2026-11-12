<?php

namespace App\Http\Controllers\Owner;

use App\Exports\DailyReportExport;
use App\Exports\WeeklyReportExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\DailyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private readonly DailyReportService $reports) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('owner.reports.index', [
            'report' => $this->reports->build($filters['date'], $filters['branch_id']),
            'branches' => Branch::query()->orderBy('name')->get(),
            'date' => $filters['date'],
            'branchId' => $filters['branch_id'],
        ]);
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->reports->build($filters['date'], $filters['branch_id']);

        $pdf = Pdf::loadView('owner.reports.pdf', ['report' => $report])->setPaper('a4');

        return $pdf->download($this->filename($report, 'pdf'));
    }

    public function excel(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $report = $this->reports->build($filters['date'], $filters['branch_id']);

        $path = storage_path('app/daily-report-'.Str::uuid().'.xlsx');

        (new DailyReportExport($report))->writeTo($path);

        return response()
            ->download($path, $this->filename($report, 'xlsx'))
            ->deleteFileAfterSend(true);
    }

    public function weekly(Request $request): View
    {
        $filters = $this->weekFilters($request);

        return view('owner.reports.weekly', [
            'report' => $this->reports->buildWeekly($filters['week'], $filters['branch_id']),
            'branches' => Branch::query()->orderBy('name')->get(),
            'week' => $filters['week'],
            'branchId' => $filters['branch_id'],
        ]);
    }

    public function weeklyPdf(Request $request)
    {
        $filters = $this->weekFilters($request);
        $report = $this->reports->buildWeekly($filters['week'], $filters['branch_id']);

        $pdf = Pdf::loadView('owner.reports.weekly-pdf', ['report' => $report])->setPaper('a4');

        return $pdf->download($this->weeklyFilename($report, 'pdf'));
    }

    public function weeklyExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->weekFilters($request);
        $report = $this->reports->buildWeekly($filters['week'], $filters['branch_id']);

        $path = storage_path('app/weekly-report-'.Str::uuid().'.xlsx');

        (new WeeklyReportExport($report))->writeTo($path);

        return response()
            ->download($path, $this->weeklyFilename($report, 'xlsx'))
            ->deleteFileAfterSend(true);
    }

    /**
     * @return array{date: string, branch_id: int|null}
     */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        return [
            'date' => $data['date'] ?? today()->toDateString(),
            'branch_id' => isset($data['branch_id']) ? (int) $data['branch_id'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function filename(array $report, string $extension): string
    {
        $branch = $report['branch'] ? '-'.Str::slug($report['branch']->name) : '';

        return 'laporan-harian-'.$report['date'].$branch.'.'.$extension;
    }

    /**
     * @return array{week: string, branch_id: int|null}
     */
    private function weekFilters(Request $request): array
    {
        $data = $request->validate([
            'week' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        return [
            'week' => $data['week'] ?? today()->toDateString(),
            'branch_id' => isset($data['branch_id']) ? (int) $data['branch_id'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function weeklyFilename(array $report, string $extension): string
    {
        $branch = $report['branch'] ? '-'.Str::slug($report['branch']->name) : '';

        return 'laporan-mingguan-'.$report['week_start'].'-sd-'.$report['week_end'].$branch.'.'.$extension;
    }
}
