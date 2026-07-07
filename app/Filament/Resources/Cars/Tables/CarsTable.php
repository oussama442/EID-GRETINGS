<?php

namespace App\Filament\Resources\Cars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand')
                    ->label(__('Brand'))
                    ->searchable(),
                TextColumn::make('model')
                    ->label(__('Model'))
                    ->searchable(),
                TextColumn::make('year')
                    ->label(__('Year'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('plate_number')
                    ->label(__('Plate number'))
                    ->searchable(),
                TextColumn::make('vin')
                    ->label(__('VIN'))
                    ->searchable(),
                TextColumn::make('category')
                    ->label(__('Category'))
                    ->searchable(),
                TextColumn::make('color')
                    ->label(__('Color'))
                    ->searchable(),
                TextColumn::make('transmission')
                    ->label(__('Transmission'))
                    ->searchable(),
                TextColumn::make('fuel_type')
                    ->label(__('Fuel type'))
                    ->searchable(),
                TextColumn::make('seats')
                    ->label(__('Seats'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('daily_rate')
                    ->label(__('Daily rate'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weekly_rate')
                    ->label(__('Weekly rate'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('monthly_rate')
                    ->label(__('Monthly rate'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mileage')
                    ->label(__('Mileage'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'rented' => 'info',
                        'reserved' => 'warning',
                        'maintenance' => 'danger',
                        'out_of_service' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('insurance_expiry')
                    ->label(__('Insurance expiry'))
                    ->date()
                    ->sortable(),
                TextColumn::make('registration_expiry')
                    ->label(__('Registration expiry'))
                    ->date()
                    ->sortable(),
                TextColumn::make('last_service_date')
                    ->label(__('Last service date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('next_service_due')
                    ->label(__('Next service due'))
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'available' => __('Available'),
                        'rented' => __('Rented'),
                        'reserved' => __('Reserved'),
                        'maintenance' => __('Maintenance'),
                        'out_of_service' => __('Out of service'),
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('send_to_maintenance')
                    ->label(__('Send to maintenance'))
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->visible(fn ($record): bool => $record->status === 'available')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->default(now()),
                        \Filament\Forms\Components\Select::make('type')
                            ->label(__('Maintenance type'))
                            ->options([
                                'oil_change' => __('Oil change'),
                                'tire_replacement' => __('Tire replacement'),
                                'repair' => __('Repair'),
                                'inspection' => __('Inspection'),
                                'cleaning' => __('Cleaning'),
                                'other' => __('Other'),
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('cost')
                            ->label(__('Cost'))
                            ->numeric()
                            ->prefix('DZD')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('odometer')
                            ->label(__('Odometer (mileage)'))
                            ->numeric()
                            ->suffix('km'),
                        \Filament\Forms\Components\TextInput::make('performed_by')
                            ->label(__('Performed by'))
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label(__('Description'))
                            ->columnSpanFull(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->maintenanceLogs()->create($data);
                        $record->update(['status' => 'maintenance']);
                    })
                    ->requiresConfirmation(),
                \Filament\Actions\Action::make('return_from_maintenance')
                    ->label(__('Return from maintenance'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'maintenance')
                    ->action(fn ($record) => $record->update(['status' => 'available']))
                    ->requiresConfirmation(),
                \Filament\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
