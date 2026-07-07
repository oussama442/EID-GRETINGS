<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('client.full_name')
                    ->label(__('Client'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('car.model')
                    ->label(__('Car'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('agent.name')
                    ->label(__('Agent'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('pickup_datetime')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('return_datetime_planned')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('return_datetime_actual')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('pickup_location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('return_location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('daily_rate_agreed')
                    ->label(__('Daily rate'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->label(__('Total amount'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('deposit_amount')
                    ->label(__('Deposit'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount')
                    ->label(__('Discount'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reserved' => 'warning',
                        'active' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('pickup_mileage')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('return_mileage')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pickup_fuel_level')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('return_fuel_level')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('contract_pdf_path')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'reserved' => __('Reserved'),
                        'active' => __('Active'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('mark_as_returned')
                    ->label(__('Mark as returned'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading(__('Mark as returned and summary'))
                    ->modalSubmitActionLabel(__('Done'))
                    ->modalCancelActionLabel(__('Cancel'))
                    ->form([
                        \Filament\Forms\Components\Placeholder::make('summary')
                            ->label(__('Billing summary'))
                            ->content(function (\App\Models\Booking $record) {
                                $days = (int) ceil(\Carbon\Carbon::parse($record->pickup_datetime)->diffInDays(now(), true));
                                if ($days === 0) {
                                    $days = 1;
                                }
                                $total = ($days * $record->daily_rate_agreed) - $record->discount;

                                return "{$record->daily_rate_agreed} DZD / day x {$days} days = " . number_format($total, 2) . " DZD (Discount: {$record->discount} DZD)";
                            }),
                        \Filament\Forms\Components\TextInput::make('return_mileage')
                            ->label(__('Return mileage'))
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('return_fuel_level')
                            ->label(__('Return fuel level')),
                        \Filament\Forms\Components\TextInput::make('payment_amount')
                            ->label(__('Add payment (DZD)'))
                            ->numeric(),
                        \Filament\Forms\Components\Select::make('payment_method')
                            ->label(__('Payment method'))
                            ->options([
                                'cash' => __('Cash'),
                                'credit_card' => __('Credit card'),
                                'bank_transfer' => __('Bank transfer'),
                                'check' => __('Check'),
                            ])
                            ->default('cash'),
                    ])
                    ->action(function (array $data, \App\Models\Booking $record) {
                        $updateData = [
                            'status' => 'completed',
                            'return_datetime_actual' => now(),
                        ];

                        if (! empty($data['return_mileage'])) {
                            $updateData['return_mileage'] = $data['return_mileage'];
                        }

                        if (! empty($data['return_fuel_level'])) {
                            $updateData['return_fuel_level'] = $data['return_fuel_level'];
                        }

                        $record->update($updateData);

                        if ($record->car) {
                            $carUpdateData = ['status' => 'available'];

                            if (! empty($data['return_mileage'])) {
                                $carUpdateData['mileage'] = $data['return_mileage'];
                            }

                            $record->car->update($carUpdateData);
                        }

                        if (! empty($data['payment_amount']) && $data['payment_amount'] > 0) {
                            \App\Models\Payment::create([
                                'booking_id' => $record->id,
                                'amount' => $data['payment_amount'],
                                'method' => $data['payment_method'] ?? 'cash',
                                'type' => 'final',
                                'paid_at' => now(),
                                'recorded_by' => auth()->id(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title(__('Car marked as returned'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (\App\Models\Booking $record) => $record->status === 'active'),
                \Filament\Actions\Action::make('downloadContract')
                    ->label(__('Contract PDF'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (\App\Models\Booking $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.contract', ['booking' => $record]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'contrat_' . $record->reference_number . '.pdf');
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
