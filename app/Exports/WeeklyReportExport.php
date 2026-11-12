<?php

namespace App\Exports;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class WeeklyReportExport
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(private readonly array $report) {}

    public function writeTo(string $path): void
    {
        $writer = new Writer;
        $writer->openToFile($path);

        $writer->getCurrentSheet()->setName('Ringkasan Mingguan');
        $this->writeSummary($writer);

        $writer->addNewSheetAndMakeItCurrent()->setName('Rekap Harian');
        $this->writeDays($writer);

        $writer->addNewSheetAndMakeItCurrent()->setName('Pembayaran');
        $this->writePayments($writer);

        $writer->addNewSheetAndMakeItCurrent()->setName('Produk Terjual');
        $this->writeProducts($writer);

        $writer->close();
    }

    private function writeSummary(Writer $writer): void
    {
        $summary = $this->report['summary'];
        $comparison = $this->report['comparison'];

        $this->title($writer, 'Laporan Mingguan - Jagung Bakar WJT');

        $writer->addRow(Row::fromValues(['Periode', $this->report['week_label']]));
        $writer->addRow(Row::fromValues(['Minggu ke', $this->report['week_number']]));
        $writer->addRow(Row::fromValues(['Cabang', $this->report['branch_label']]));
        $writer->addRow(Row::fromValues([]));

        $this->section($writer, 'Ringkasan');
        $this->moneyRows($writer, [
            ['Uang masuk (pembayaran)', $summary['revenue']],
            ['Nilai penjualan (pesanan)', $summary['gross_sales']],
            ['Total diskon', $summary['discounts']],
            ['Sisa tagihan', $summary['outstanding']],
            ['Rata-rata per hari', $summary['average_per_day']],
        ]);

        $writer->addRow(Row::fromValues(['Total pesanan', $summary['orders_total']]));
        $writer->addRow(Row::fromValues(['Pesanan selesai', $summary['orders_completed']]));
        $writer->addRow(Row::fromValues(['Pesanan berjalan', $summary['orders_active']]));
        $writer->addRow(Row::fromValues(['Pesanan dibatalkan', $summary['orders_cancelled']]));
        $writer->addRow(Row::fromValues(['Transaksi dibayar', $summary['paid_orders']]));
        $writer->addRow(Row::fromValues(['Hari terbaik', $summary['best_day_label']]));
        $writer->addRow(Row::fromValues(
            ['Pendapatan hari terbaik', $summary['best_day_revenue']],
            $this->money(),
        ));

        $writer->addRow(Row::fromValues([]));
        $this->section($writer, 'Perbandingan Minggu Sebelumnya');
        $this->moneyRows($writer, [
            ['Uang masuk minggu lalu', $comparison['previous_revenue']],
            ['Selisih uang masuk', $comparison['revenue_delta']],
        ]);

        $percent = $comparison['revenue_delta_percent'];
        $writer->addRow(Row::fromValues([
            'Perubahan uang masuk',
            $percent === null ? '-' : number_format($percent, 1, ',', '.').'%',
        ]));
        $writer->addRow(Row::fromValues(['Pesanan minggu lalu', $comparison['previous_orders']]));
        $writer->addRow(Row::fromValues(['Selisih pesanan', $comparison['orders_delta']]));

        $writer->getCurrentSheet()->setColumnWidth(34, 1);
        $writer->getCurrentSheet()->setColumnWidth(24, 2);
    }

    private function writeDays(Writer $writer): void
    {
        $this->header($writer, [
            'Hari',
            'Tanggal',
            'Pesanan',
            'Dibayar',
            'Uang Masuk',
            'Nilai Pesanan',
            'Batal',
        ]);

        foreach ($this->report['days'] as $day) {
            $writer->addRow(Row::fromValues([
                $day['day_label'],
                $day['date_label'],
                $day['orders_total'],
                $day['paid_orders'],
                $day['revenue'],
                $day['gross_sales'],
                $day['orders_cancelled'],
            ]));
        }

        $summary = $this->report['summary'];

        $writer->addRow(Row::fromValues([
            'Total',
            '',
            $summary['orders_total'],
            $summary['paid_orders'],
            $summary['revenue'],
            $summary['gross_sales'],
            $summary['orders_cancelled'],
        ], $this->bold()));

        $writer->getCurrentSheet()->setColumnWidth(12, 1);
        $writer->getCurrentSheet()->setColumnWidth(16, 2);
        $writer->getCurrentSheet()->setColumnWidth(20, 5, 6);
    }

    private function writePayments(Writer $writer): void
    {
        $this->header($writer, ['Metode', 'Jumlah Transaksi', 'Total Diterima']);

        foreach ($this->report['methods'] as $method) {
            $writer->addRow(Row::fromValues([
                $method['label'],
                $method['count'],
                $method['amount'],
            ]));
        }

        $writer->addRow(Row::fromValues([
            'Total',
            $this->report['summary']['paid_orders'],
            $this->report['summary']['revenue'],
        ], $this->bold()));

        $writer->getCurrentSheet()->setColumnWidth(20, 1);
        $writer->getCurrentSheet()->setColumnWidth(20, 2);
        $writer->getCurrentSheet()->setColumnWidth(22, 3);
    }

    private function writeProducts(Writer $writer): void
    {
        $this->header($writer, ['Produk', 'Varian', 'Qty Terjual', 'Pendapatan']);

        foreach ($this->report['products'] as $product) {
            $writer->addRow(Row::fromValues([
                $product['name'],
                $product['variant'] ?: '-',
                $product['quantity'],
                $product['revenue'],
            ]));
        }

        if ($this->report['products']->isEmpty()) {
            $writer->addRow(Row::fromValues(['Belum ada produk terjual pada minggu ini.']));
        }

        $writer->getCurrentSheet()->setColumnWidth(28, 1);
        $writer->getCurrentSheet()->setColumnWidth(20, 2);
        $writer->getCurrentSheet()->setColumnWidth(16, 3);
        $writer->getCurrentSheet()->setColumnWidth(20, 4);
    }

    /**
     * @param  list<array{0: string, 1: float}>  $rows
     */
    private function moneyRows(Writer $writer, array $rows): void
    {
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValuesWithStyles($row, null, [1 => $this->money()]));
        }
    }

    private function title(Writer $writer, string $text): void
    {
        $writer->addRow(Row::fromValues([$text], (new Style)->setFontBold()->setFontSize(15)));
    }

    private function section(Writer $writer, string $text): void
    {
        $writer->addRow(Row::fromValues([$text], (new Style)->setFontBold()->setFontSize(12)));
    }

    /**
     * @param  list<string>  $columns
     */
    private function header(Writer $writer, array $columns): void
    {
        $style = (new Style)->setFontBold()->setBackgroundColor('F3F4F6');

        $writer->addRow(Row::fromValues($columns, $style));

        foreach (array_keys($columns) as $index) {
            $writer->getCurrentSheet()->setColumnWidth(18, $index + 1);
        }
    }

    private function bold(): Style
    {
        return (new Style)->setFontBold();
    }

    private function money(): Style
    {
        return (new Style)->setFormat('#,##0');
    }
}
