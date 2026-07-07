<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCar extends ViewRecord
{
    protected static string $resource = CarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('send_to_maintenance')
                ->label(__('Send to maintenance'))
                ->icon('heroicon-o-wrench')
                ->color('warning')
                ->visible(fn ($record) => $record->status === 'available')
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
                ->action(function ($record, array $data) {
                    $record->maintenanceLogs()->create($data);
                    $record->update(['status' => 'maintenance']);
                })
                ->requiresConfirmation(),
            \Filament\Actions\Action::make('return_from_maintenance')
                ->label(__('Return from maintenance'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'maintenance')
                ->action(function ($record) {
                    $record->update(['status' => 'available']);
                })
                ->requiresConfirmation(),
            EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\Cars\Widgets\CarRevenueChart::class,
        ];
    }
}
