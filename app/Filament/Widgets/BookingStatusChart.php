<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Support\BranchAccess;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BookingStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 2,
    ];

    protected string $color = 'warning';

    public function getHeading(): string
    {
        return __('Booking status mix');
    }

    public function getDescription(): ?string
    {
        return __('Reservation health for the selected period.');
    }

    protected function getData(): array
    {
        $statuses = [
            'reserved' => __('Reserved'),
            'active' => __('Active'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
            'overdue' => __('Overdue'),
        ];

        $labels = [];
        $data = [];

        foreach ($statuses as $status => $label) {
            $count = (clone $this->bookingQuery())
                ->where('status', $status)
                ->count();

            if ($count === 0 && filled($this->filters['bookingStatus'] ?? null)) {
                continue;
            }

            $labels[] = $label;
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Bookings'),
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b',
                        '#38bdf8',
                        '#22c55e',
                        '#ef4444',
                        '#f97316',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    private function bookingQuery(): Builder
    {
        [$startDate, $endDate] = $this->dateRange();

        return BranchAccess::scope(Booking::query())
            ->when($this->filters['branchId'] ?? null, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->when($this->filters['bookingStatus'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($startDate, fn (Builder $query) => $query->where('pickup_datetime', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->where('pickup_datetime', '<=', $endDate));
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
