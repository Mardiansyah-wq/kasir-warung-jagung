<?php

namespace App\Exports;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class DailyReportExport
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(private readonly array $report) {}

    public function writeTo(string $path): void
    {
        $writer = new Writer;
        $writer->openToFile($path);

        $writer->getCurrentSheet()->setName('Ringkasan');
        $this->writeSummary($writer);

        $writer->addNewSheetAndMakeItCurrent()->setName('Rincian Pesanan');
        $this->writeOrders($writer);

        $writer->addNewSheetAndMakeItCurrent()->setName('Pembayaran');
        $this->writePayments($writer);

        $writer->addNewSheetAndMakeItCurrent()->setName('Produk Terjual');
        $this->writeProducts($writer);

        $writer->close();
    }

    private function writeSummary(Writer $writer): void
    {
        $summary = $this->report['summary'];

        $this->title($writer, 'Laporan Harian - Jagung Bakar WJT');

        $writer->addRow(Row::fromValues(['Tanggal', $this->report['date_label']]));
        $writer->addRow(Row::fromValues(['Cabang', $this->report['branch_label']]));
        $writer->addRow(Row::fromValues([]));

        $this->section($writer, 'Ringkasan');

        $rows = [
            ['Total pesanan', $summary['orders_total']],
            ['Pesanan selesai', $summary['orders_completed']],
            ['Pesanan berjalan', $summary['orders_active']],
            ['Pesanan dibatalkan', $summary['orders_cancelled']],
            ['Nilai penjualan (pesanan)', $summary['gross_sales']],
            ['Total diskon', $summary['discounts']],
            ['Uang masuk (pembayaran)', $summary['revenue']],
            ['Transaksi dibayar', $summary['paid_orders']],
            ['Rata-rata per transaksi', $summary['average']],
            ['Sisa tagihan', $summary['outstanding']],
        ];

        foreach ($rows as $row) {
            $cellStyle = is_float($row[1]) ? $this->money() : new Style;
            $writer->addRow(Row::fromValuesWithStyles($row, null, [1 => $cellStyle]));
        }

        $writer->getCurrentSheet()->setColumnWidth(32, 1);
        $writer->getCurrentSheet()->setColumnWidth(24, 2);
    }

    private function writeOrders(Writer $writer): void
    {
        $this->header($writer, [
            'Antrian',
            'Kode',
            'Pelanggan',
            'Cabang',
            'Waktu',
            'Kasir',
            'Tipe',
            'Item',
            'Total',
            'Dibayar',
            'Sisa',
            'Status',
            'Pembayaran',
        ]);

        foreach ($this->report['orders'] as $order) {
            $cancelled = $order->isCancelled();

            $writer->addRow(Row::fromValues([
                $order->queueLabel(),
                $order->order_code,
                $order->customer_name,
                $order->branch?->name ?? '-',
                $order->created_at?->format('d/m/Y H:i') ?? '-',
                $order->creator?->name ?? '-',
                $order->orderTypeLabel(),
                (int) $order->items_count,
                (float) $order->total_amount,
                $cancelled ? 0.0 : $order->paidAmount(),
                $cancelled ? 0.0 : $order->balanceDue(),
                $order->statusLabel(),
                $cancelled ? '-' : $order->paymentStatusLabel(),
            ]));
        }

        $writer->getCurrentSheet()->setColumnWidth(10, 1);
        $writer->getCurrentSheet()->setColumnWidth(20, 2);
        $writer->getCurrentSheet()->setColumnWidth(24, 3);
        $writer->getCurrentSheet()->setColumnWidth(18, 4);
        $writer->getCurrentSheet()->setColumnWidth(18, 7);
        $writer->getCurrentSheet()->setColumnWidthForRange(14, 9, 11);
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
            $writer->addRow(Row::fromValues(['Belum ada produk terjual pada tanggal ini.']));
        }

        $writer->getCurrentSheet()->setColumnWidth(28, 1);
        $writer->getCurrentSheet()->setColumnWidth(20, 2);
        $writer->getCurrentSheet()->setColumnWidth(16, 3);
        $writer->getCurrentSheet()->setColumnWidth(20, 4);
    }

    private function title(Writer $writer, string $text): void
    {
        $style = (new Style)
            ->setFontBold()
            ->setFontSize(15);

        $writer->addRow(Row::fromValues([$text], $style));
    }

    private function section(Writer $writer, string $text): void
    {
        $style = (new Style)
            ->setFontBold()
            ->setFontSize(12);

        $writer->addRow(Row::fromValues([$text], $style));
    }

    /**
     * @param  list<string>  $columns
     */
    private function header(Writer $writer, array $columns): void
    {
        $style = (new Style)
            ->setFontBold()
            ->setBackgroundColor('F3F4F6');

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
