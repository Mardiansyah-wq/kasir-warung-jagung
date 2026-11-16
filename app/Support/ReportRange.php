<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Rentang tanggal ringkasan penjualan beserta periode pembandingnya.
 *
 * Hanya tiga preset yang didukung supaya angka di dashboard selalu punya
 * pembanding yang jelas: hari ini, 7 hari terakhir, dan bulan berjalan.
 */
final class ReportRange
{
    public const TODAY = 'today';

    public const WEEK = '7d';

    public const MONTH = 'month';

    /** @var list<string> */
    public const PRESETS = [self::TODAY, self::WEEK, self::MONTH];

    private function __construct(
        public readonly string $preset,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly CarbonImmutable $previousStart,
        public readonly CarbonImmutable $previousEnd,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $preset = $request->query('range');

        return self::make(is_string($preset) ? $preset : null);
    }

    public static function make(?string $preset, ?CarbonImmutable $today = null): self
    {
        $today = ($today ?? CarbonImmutable::now())->startOfDay();
        $preset = in_array($preset, self::PRESETS, true) ? $preset : self::WEEK;

        return match ($preset) {
            self::TODAY => new self($preset, $today, $today, $today->subDay(), $today->subDay()),
            self::MONTH => self::monthToDate($today),
            default => self::trailingDays($today, 7),
        };
    }

    /**
     * Jumlah hari dalam rentang, termasuk hari pertama dan terakhir.
     */
    public function days(): int
    {
        return (int) $this->start->diffInDays($this->end) + 1;
    }

    public function isToday(): bool
    {
        return $this->preset === self::TODAY;
    }

    public function label(): string
    {
        if ($this->start->isSameDay($this->end)) {
            return $this->start->locale('id')->isoFormat('dddd, D MMMM Y');
        }

        if ($this->start->month === $this->end->month && $this->start->year === $this->end->year) {
            return $this->start->locale('id')->isoFormat('D')
                .' – '.$this->end->locale('id')->isoFormat('D MMMM Y');
        }

        return $this->start->locale('id')->isoFormat('D MMM')
            .' – '.$this->end->locale('id')->isoFormat('D MMM Y');
    }

    public function previousLabel(): string
    {
        return match ($this->preset) {
            self::TODAY => 'vs kemarin',
            self::MONTH => 'vs bulan lalu',
            default => 'vs 7 hari lalu',
        };
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'preset' => $this->preset,
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
            'label' => $this->label(),
            'previous_start' => $this->previousStart->toDateString(),
            'previous_end' => $this->previousEnd->toDateString(),
            'previous_label' => $this->previousLabel(),
        ];
    }

    private static function trailingDays(CarbonImmutable $today, int $days): self
    {
        $start = $today->subDays($days - 1);

        return new self(
            self::WEEK,
            $start,
            $today,
            $start->subDays($days),
            $start->subDay(),
        );
    }

    private static function monthToDate(CarbonImmutable $today): self
    {
        $start = $today->startOfMonth();
        $previousStart = $start->subMonthNoOverflow();
        $previousEnd = $previousStart
            ->addDays((int) $start->diffInDays($today))
            ->min($previousStart->endOfMonth());

        return new self(self::MONTH, $start, $today, $previousStart, $previousEnd);
    }
}
