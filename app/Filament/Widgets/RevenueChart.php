<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Support\BranchAccess;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 2,
    ];

    protected string $color = 'success';

    public function getHeading(): string
    {
        return __('Revenue by month');
    }

    public function getDescription(): ?string
    {
        return __('Collected payments for the selected dashboard filters.');
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->dateRange();
        $startDate ??= now()->subMonths(5)->startOfMonth();
        $endDate ??= now()->endOfMonth();

        if ($startDate->diffInMonths($endDate) > 11) {
            $startDate = $endDate->copy()->subMonths(11)->startOfMonth();
        }

        $labels = [];
        $data = [];
        $cursor = $startDate->copy()->startOfMonth();

        while ($cursor->lte($endDate)) {
            $periodStart = $cursor->copy()->startOfMonth();
            $periodEnd = $cursor->copy()->endOfMonth();

            $labels[] = $cursor->translatedFormat('M Y');
            $data[] = (float) $this->paymentQuery($periodStart, $periodEnd)->sum('amount');

            $cursor->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => __('Revenue (DZD)'),
                    'data' => $data,
                    'backgroundColor' => '#22c55e',
                    'borderColor' => '#16a34a',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function paymentQuery(Carbon $startDate, Carbon $endDate): Builder
    {
        $branchId = $this->filters['branchId'] ?? null;
        $bookingStatus = $this->filters['bookingStatus'] ?? null;
        $branchRestricted = BranchAccess::isRestricted();

        return Payment::query()
            ->where(function (Builder $query) use ($startDate, $endDate): void {
                $query
                    ->whereBetween('paid_at', [$startDate, $endDate])
                    ->orWhere(function (Builder $query) use ($startDate, $endDate): void {
                        $query
                            ->whereNull('paid_at')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->when($branchId || $bookingStatus || $branchRestricted, function (Builder $query) use ($branchId, $bookingStatus): void {
                $query->whereHas('booking', function (Builder $query) use ($branchId, $bookingStatus): void {
                    BranchAccess::scope($query)
                        ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
                        ->when($bookingStatus, fn (Builder $query) => $query->where('status', $bookingStatus));
                });
            });
    }

    private function dateRange(): array
    {
        $startDate = filled($this->filters['startDate'] ?? null)
            ? Carbon::parse($this->filters['startDate'])->startOfDay()
            : null;

        $endDate = filled($this->filters['endDate'] ?? null)
            ? Carbon::parse($this->filters['endDate'])->endOfDay()
            : null;

        return [$startDate, $endDate];
    }
}
