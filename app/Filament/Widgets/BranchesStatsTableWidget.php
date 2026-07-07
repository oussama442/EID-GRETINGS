<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Payment;
use App\Support\BranchAccess;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BranchesStatsTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('Branch performance');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Branch::query()
                    ->when(BranchAccess::isRestricted(), fn (Builder $query): Builder => $query->whereKey(BranchAccess::branchId()))
                    ->when($this->filters['branchId'] ?? null, fn (Builder $query, $branchId) => $query->whereKey($branchId))
                    ->withCount('cars')
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('Branch'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label(__('City'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('available_cars')
                    ->label(__('Available cars'))
                    ->state(fn (Branch $record): string => $record->cars()->where('status', 'available')->count() . ' / ' . $record->cars_count)
                    ->badge()
                    ->color('success'),
                TextColumn::make('rented_reserved_cars')
                    ->label(__('Rented / reserved'))
                    ->state(fn (Branch $record): string => $record->cars()->where('status', 'rented')->count() . ' / ' . $record->cars()->where('status', 'reserved')->count())
                    ->badge()
                    ->color('info'),
                TextColumn::make('maintenance_cars')
                    ->label(__('Maintenance'))
                    ->state(fn (Branch $record): int => $record->cars()->whereIn('status', ['maintenance', 'out_of_service'])->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('period_bookings')
                    ->label(__('Bookings'))
                    ->state(fn (Branch $record): int => $this->bookingQuery($record)->count())
                    ->sortable(false),
                TextColumn::make('active_overdue')
                    ->label(__('Active / overdue'))
                    ->state(fn (Branch $record): string => $this->activeBookingsQuery($record)->count() . ' / ' . $this->overdueBookingsQuery($record)->count())
                    ->badge()
                    ->color(fn (string $state): string => str_ends_with($state, '/ 0') ? 'info' : 'danger'),
                TextColumn::make('revenue_collected')
                    ->label(__('Revenue'))
                    ->state(fn (Branch $record): string => $this->money($this->paymentQuery($record)->sum('amount')))
                    ->alignEnd(),
                TextColumn::make('outstanding_balance')
                    ->label(__('Outstanding'))
                    ->state(function (Branch $record): string {
                        $expected = $this->bookingQuery($record)
                            ->where('status', '!=', 'cancelled')
                            ->sum('total_amount');
                        $paid = $this->paymentQuery($record)->sum('amount');

                        return $this->money(max((float) $expected - (float) $paid, 0));
                    })
                    ->alignEnd(),
            ])
            ->paginated(false);
    }

    private function bookingQuery(Branch $branch): Builder
    {
        [$startDate, $endDate] = $this->dateRange();

        return Booking::query()
            ->where('branch_id', $branch->getKey())
            ->when($this->filters['bookingStatus'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->when($startDate, fn (Builder $query) => $query->where('pickup_datetime', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->where('pickup_datetime', '<=', $endDate));
    }

    private function activeBookingsQuery(Branch $branch): Builder
    {
        return Booking::query()
            ->where('branch_id', $branch->getKey())
            ->where('status', 'active');
    }

    private function overdueBookingsQuery(Branch $branch): Builder
    {
        return Booking::query()
            ->where('branch_id', $branch->getKey())
            ->whereIn('status', ['active', 'overdue'])
            ->where('return_datetime_planned', '<', now());
    }

    private function paymentQuery(Branch $branch): Builder
    {
        [$startDate, $endDate] = $this->dateRange();
        $bookingStatus = $this->filters['bookingStatus'] ?? null;

        return Payment::query()
            ->whereHas('booking', function (Builder $query) use ($branch, $bookingStatus): void {
                $query
                    ->where('branch_id', $branch->getKey())
                    ->when($bookingStatus, fn (Builder $query) => $query->where('status', $bookingStatus));
            })
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
        return number_format((float) $amount, 0) . ' DZD';
    }
}
