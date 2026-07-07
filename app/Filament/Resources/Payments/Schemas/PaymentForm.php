<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Support\BranchAccess;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('booking_id')
                    ->label(__('Booking'))
                    ->relationship('booking', 'reference_number', fn (Builder $query): Builder => BranchAccess::scope($query))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('amount')
                    ->label(__('Amount'))
                    ->required()
                    ->numeric(),
                Select::make('method')
                    ->label(__('Payment method'))
                    ->options([
                        'cash' => __('Cash'),
                        'card' => __('Credit card'),
                        'bank_transfer' => __('Bank transfer'),
                    ])
                    ->required(),
                Select::make('type')
                    ->label(__('Payment type'))
                    ->options([
                        'deposit' => __('Deposit'),
                        'rental_fee' => __('Rental fee'),
                        'final' => __('Final payment'),
                        'penalty' => __('Penalty'),
                        'refund' => __('Refund'),
                    ])
                    ->required(),
                DateTimePicker::make('paid_at')
                    ->label(__('Paid at')),
                Select::make('recorded_by')
                    ->label(__('Recorded by'))
                    ->relationship('recordedBy', 'name', fn (Builder $query): Builder => BranchAccess::scope($query))
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => auth()->id()),
                TextInput::make('receipt_number')
                    ->label(__('Receipt number')),
                TextInput::make('receipt_pdf_path')
                    ->label(__('Receipt PDF path')),
            ]);
    }
}
