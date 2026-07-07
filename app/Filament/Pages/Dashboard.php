<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Support\BranchAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function getTitle(): string | Htmlable
    {
        return __('Dashboard');
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Dashboard filters'))
                    ->description(__('Track availability, rentals, revenue, and branch performance for the selected period.'))
                    ->icon('heroicon-o-funnel')
                    ->compact()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(__('Start date'))
                            ->default(now()->startOfMonth()),
                        DatePicker::make('endDate')
                            ->label(__('End date'))
                            ->default(now()->endOfMonth()),
                        Select::make('branchId')
                            ->label(__('Branch'))
                            ->options(fn (): array => Branch::query()
                                ->when(BranchAccess::isRestricted(), fn (Builder $query): Builder => $query->whereKey(BranchAccess::branchId()))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->default(fn (): ?int => BranchAccess::isRestricted() ? BranchAccess::branchId() : null)
                            ->disabled(fn (): bool => BranchAccess::isRestricted())
                            ->dehydrated()
                            ->placeholder(__('All branches'))
                            ->searchable()
                            ->preload(),
                        Select::make('bookingStatus')
                            ->label(__('Booking status'))
                            ->options([
                                'reserved' => __('Reserved'),
                                'active' => __('Active'),
                                'completed' => __('Completed'),
                                'cancelled' => __('Cancelled'),
                                'overdue' => __('Overdue'),
                            ])
                            ->placeholder(__('All bookings')),
                        Select::make('carStatus')
                            ->label(__('Car status'))
                            ->options([
                                'available' => __('Available'),
                                'rented' => __('Rented'),
                                'reserved' => __('Reserved'),
                                'maintenance' => __('Maintenance'),
                                'out_of_service' => __('Out of service'),
                            ])
                            ->placeholder(__('All cars')),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 5,
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
