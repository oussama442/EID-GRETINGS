<?php

namespace App\Filament\Resources\Cars;

use App\Filament\Resources\Cars\Pages\CreateCar;
use App\Filament\Resources\Cars\Pages\EditCar;
use App\Filament\Resources\Cars\Pages\ListCars;
use App\Filament\Resources\Cars\Schemas\CarForm;
use App\Filament\Resources\Cars\Tables\CarsTable;
use App\Models\Car;
use App\Support\BranchAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'plate_number';

    public static function getNavigationLabel(): string
    {
        return __('Cars');
    }

    public static function getModelLabel(): string
    {
        return __('Car');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Cars');
    }

    public static function form(Schema $schema): Schema
    {
        return CarForm::configure($schema);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('Car details'))
                    ->components([
                        \Filament\Infolists\Components\TextEntry::make('brand')
                            ->label(__('Brand')),
                        \Filament\Infolists\Components\TextEntry::make('model')
                            ->label(__('Model')),
                        \Filament\Infolists\Components\TextEntry::make('plate_number')
                            ->label(__('Plate number')),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'available' => 'success',
                                'rented' => 'info',
                                'reserved' => 'warning',
                                'maintenance' => 'danger',
                                'out_of_service' => 'gray',
                                default => 'gray',
                            }),
                    ])->columns(4),
                \Filament\Schemas\Components\Section::make(__('Analytics'))
                    ->components([
                        \Filament\Infolists\Components\TextEntry::make('bookings_count')
                            ->state(fn ($record) => $record->bookings()->count())
                            ->label(__('Total rentals')),
                        \Filament\Infolists\Components\TextEntry::make('total_revenue')
                            ->state(fn ($record) => number_format($record->bookings()->where('status', '!=', 'cancelled')->sum('total_amount'), 2) . ' DZD')
                            ->label(__('Total revenue')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return CarsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return BranchAccess::scope(parent::getEloquentQuery());
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Cars\RelationManagers\BookingsRelationManager::class,
            \App\Filament\Resources\Cars\RelationManagers\MaintenanceLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCars::route('/'),
            'create' => CreateCar::route('/create'),
            'view' => \App\Filament\Resources\Cars\Pages\ViewCar::route('/{record}'),
            'edit' => EditCar::route('/{record}/edit'),
        ];
    }
}
