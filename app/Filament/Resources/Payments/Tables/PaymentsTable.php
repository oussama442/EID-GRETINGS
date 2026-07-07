<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking.reference_number')
                    ->label(__('Booking reference'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('method')
                    ->label(__('Payment method'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Payment type'))
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label(__('Paid at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('recordedBy.name')
                    ->label(__('Recorded by'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('receipt_number')
                    ->label(__('Receipt number'))
                    ->searchable(),
                TextColumn::make('receipt_pdf_path')
                    ->label(__('Receipt PDF path'))
                    ->searchable(),
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
            ->recordActions([
                \Filament\Actions\Action::make('downloadReceipt')
                    ->label(__('Receipt PDF'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Payment $record) {
                        $pdf = Pdf::loadView('pdf.receipt', ['payment' => $record]);

                        return response()->streamDownload(function () use ($pdf): void {
                            echo $pdf->output();
                        }, 'receipt_' . ($record->receipt_number ?: $record->id) . '.pdf');
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
