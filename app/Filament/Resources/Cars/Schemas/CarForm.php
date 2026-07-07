<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Support\BranchAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('brand')
                    ->label(__('Brand'))
                    ->required(),
                TextInput::make('model')
                    ->label(__('Model'))
                    ->required(),
                TextInput::make('year')
                    ->label(__('Year'))
                    ->required()
                    ->numeric(),
                TextInput::make('plate_number')
                    ->label(__('Plate number'))
                    ->required(),
                TextInput::make('vin')
                    ->label(__('VIN')),
                Select::make('category')
                    ->label(__('Category'))
                    ->options([
                        'Economy' => __('Economy'),
                        'Compact' => __('Compact'),
                        'Mid-size' => __('Mid-size'),
                        'SUV' => __('SUV'),
                        'Luxury' => __('Luxury'),
                        'Van' => __('Van'),
                    ]),
                TextInput::make('color')
                    ->label(__('Color')),
                Select::make('transmission')
                    ->label(__('Transmission'))
                    ->options([
                        'Automatic' => __('Automatic'),
                        'Manual' => __('Manual'),
                    ]),
                Select::make('fuel_type')
                    ->label(__('Fuel type'))
                    ->options([
                        'Petrol' => __('Petrol'),
                        'Diesel' => __('Diesel'),
                        'Electric' => __('Electric'),
                        'Hybrid' => __('Hybrid'),
                    ]),
                TextInput::make('seats')
                    ->label(__('Seats'))
                    ->required()
                    ->numeric()
                    ->default(5),
                TextInput::make('daily_rate')
                    ->label(__('Daily rate'))
                    ->required()
                    ->numeric(),
                TextInput::make('weekly_rate')
                    ->label(__('Weekly rate'))
                    ->numeric(),
                TextInput::make('monthly_rate')
                    ->label(__('Monthly rate'))
                    ->numeric(),
                TextInput::make('mileage')
                    ->label(__('Mileage'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'available' => __('Available'),
                        'rented' => __('Rented'),
                        'reserved' => __('Reserved'),
                        'maintenance' => __('Maintenance'),
                        'out_of_service' => __('Out of service'),
                    ])
                    ->required()
                    ->default('available'),
                Select::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn (Builder $query): Builder => BranchAccess::scope($query, 'id'))
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => BranchAccess::branchId())
                    ->disabled(fn (): bool => BranchAccess::isRestricted())
                    ->dehydrated(),
                Textarea::make('features')
                    ->label(__('Features'))
                    ->columnSpanFull(),
                DatePicker::make('insurance_expiry')
                    ->label(__('Insurance expiry')),
                DatePicker::make('registration_expiry')
                    ->label(__('Registration expiry')),
                DatePicker::make('last_service_date')
                    ->label(__('Last service date')),
                DatePicker::make('next_service_due')
                    ->label(__('Next service due')),
            ]);
    }
}
