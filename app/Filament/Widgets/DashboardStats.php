<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use App\Support\BranchAccess;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getHeading(): ?string
    {
        return __('Operations overview');
    }

    protected function getDescription(): ?string
    {
        return __('Fleet availability, rental flow, revenue, and collection risk.');
    }

    protected function getColumns(): int | array | null
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    protected function getStats(): array
    {
        $carQuery = $this->carQuery();
        $bookingQuery = $this->bookingQuery();
        $paymentQuery = $this->paymentQuery();

        $totalCars = (clone $carQuery)->count();
        $availableCars = (clone $carQuery)->where('status', 'available')->count();
        $rentedCars = (clone $carQuery)->where('status', 'rented')->count();
        $reservedCars = (clone $carQuery)->where('status', 'reserved')->count();
        $maintenanceCars = (clone $carQuery)->whereIn('status', ['maintenance', 'out_of_service'])->count();

        $bookings = (clone $bookingQuery)->count();
        $activeRentals = $this->bookingQuery(null)
            ->where('status', 'active')
            ->count();
        $overdueRentals = $this->bookingQuery(null)
            ->whereIn('status', ['active', 'overdue'])
            ->where('return_datetime_planned', '<', now())
            ->count();

        $pickupsToday = $this->bookingQuery(null)
            ->whereDate('pickup_datetime', today())
            ->whereIn('status', ['reserved', 'active'])
            ->count();
        $returnsToday = $this->bookingQuery(null)
            ->whereDate('return_datetime_planned', today())
            ->whereIn('status', ['active', 'overdue'])
            ->count();

        $revenue = (clone $paymentQuery)->sum('amount');
        $expectedRevenue = (clone $bookingQuery)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        $outstanding = max((float) $expectedRevenue - (float) $revenue, 0);
        $periodClients = (clone $bookingQuery)
            ->distinct()
            ->count('client_id');

        return [
            Stat::make(__('Available cars'), $availableCars)
                ->description(__('of :total total cars', ['total' => $totalCars]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($availableCars > 0 ? 'success' : 'danger'),
            Stat::make(__('Rented / reserved'), "{$rentedCars} / {$reservedCars}")
                ->description(__('Cars currently committed'))
                ->descriptionIcon('heroicon-m-key')
                ->color('info'),
            Stat::make(__('Maintenance'), $maintenanceCars)
                ->description(__('Unavailable for operations'))
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($maintenanceCars > 0 ? 'warning' : 'success'),
            Stat::make(__('Bookings'), $bookings)
                ->description(__('Selected period'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make(__('Active rentals'), $activeRentals)
                ->description(__('Cars currently out'))
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('info'),
            Stat::make(__('Due today'), "{$pickupsToday} / {$returnsToday}")
                ->description(__('Pickups / returns'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($overdueRentals > 0 ? 'danger' : 'warning'),
            Stat::make(__('Revenue'), $this->money($revenue))
                ->description(__('Collected in selected period'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('Outstanding'), $this->money($outstanding))
                ->description(__('Unpaid balance, :clients clients', ['clients' => $periodClients]))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outstanding > 0 ? 'danger' : 'success'),
        ];
    }

    private function carQuery(): Builder
    {
        return BranchAccess::scope(Car::query())
            ->when($this->filters['branchId'] ?? null, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->when($this->filters['carStatus'] ?? null, fn (Builder $query, $status) => $query->where('status', $status));
    }

    private function bookingQuery(?string $dateColumn = 'pickup_datetime'): Builder
    {
        [$startDate, $endDate] = $this->dateRange();

        return BranchAccess::scope(Booking::query())
            ->when($this->filters['branchId'] ?? null, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
            ->when($this->filters['bookingStatus'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($dateColumn && $startDate, fn (Builder $query) => $query->where($dateColumn, '>=', $startDate))
            ->when($dateColumn && $endDate, fn (Builder $query) => $query->where($dateColumn, '<=', $endDate));
    }

    private function paymentQuery(): Builder
    {
        [$startDate, $endDate] = $this->dateRange();
        $branchId = $this->filters['branchId'] ?? null;
        $bookingStatus = $this->filters['bookingStatus'] ?? null;
        $branchRestricted = BranchAccess::isRestricted();

        return Payment::query()
            ->when($startDate, function (Builder $query) use ($startDate): void {
                $query->where(function (Builder $query) use ($startDate): void {
                    $query
                        ->where('paid_at', '>=', $startDate)
                        ->orWhere(function (Builder $query) use ($startDate): void {
                            $query
                                ->whereNull('paid_at')
                                ->where('created_at', '>=', $startDate);
                        });
                });
            })
            ->when($endDate, function (Builder $query) use ($endDate): void {
                $query->where(function (Builder $query) use ($endDate): void {
                    $query
                        ->where('paid_at', '<=', $endDate)
                        ->orWhere(function (Builder $query) use ($endDate): void {
                            $query
                                ->whereNull('paid_at')
                                ->where('created_at', '<=', $endDate);
                        });
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

    private function money(float | int | string | null $amount): string
    {
        $amount = (float) $amount;

        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'M DZD';
        }

        if ($amount >= 1_000) {
            return number_format($amount / 1_000, 1) . 'K DZD';
        }

        return number_format($amount, 0) . ' DZD';
    }
}
