<?php

namespace App\Filament\Resources\Cars\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CarRevenueChart extends ChartWidget
{
    public ?Model $record = null;

    public function getHeading(): string
    {
        return __('Monthly revenue (last 12 months)');
    }

    protected function getData(): array
    {
        $carId = $this->record?->id;
        if (!$carId) {
            return [];
        }

        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $revenue = Booking::where('car_id', $carId)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('pickup_datetime', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('total_amount');

            $data[] = $revenue;
            $labels[] = $month->format('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => __('Revenue (DZD)'),
                    'data' => $data,
                    'backgroundColor' => '#3b82f6', // Tailwind blue-500
                    'borderColor' => '#2563eb',     // Tailwind blue-600
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
