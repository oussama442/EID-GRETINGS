<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
use App\Models\Car;
use App\Support\BranchAccess;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        $updateTotal = function ($get, $set): void {
            $pickup = $get('pickup_datetime');
            $return = $get('return_datetime_planned');
            $rate = $get('daily_rate_agreed');
            $discount = (float) ($get('discount') ?: 0);

            if ($pickup && $return && is_numeric($rate)) {
                $pickupDate = Carbon::parse($pickup);
                $returnDate = Carbon::parse($return);

                if ($returnDate->greaterThan($pickupDate)) {
                    $days = max((int) ceil($pickupDate->diffInHours($returnDate) / 24), 1);
                    $set('total_amount', max(0, ($days * (float) $rate) - $discount));
                }
            }
        };

        $getDisabledDates = function ($get, ?Model $record): array {
            $carId = $get('car_id');

            if (! $carId) {
                return [];
            }

            $dates = [];
            $bookings = Booking::query()
                ->where('car_id', $carId)
                ->whereIn('status', ['reserved', 'active'])
                ->when($record, fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))
                ->get();

            foreach ($bookings as $booking) {
                $start = Carbon::parse($booking->pickup_datetime)->startOfDay();
                $end = $booking->return_datetime_planned
                    ? Carbon::parse($booking->return_datetime_planned)->endOfDay()
                    : now()->addDays(365);

                while ($start->lte($end)) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
            }

            return array_values(array_unique($dates));
        };

        return $schema
            ->components([
                TextInput::make('reference_number')
                    ->label(__('Reference number'))
                    ->required()
                    ->default(fn (): string => 'BKG-' . strtoupper(substr(uniqid(), -6))),
                Select::make('client_id')
                    ->label(__('Client'))
                    ->relationship('client', 'full_name', function (Builder $query): Builder {
                        if (BranchAccess::isRestricted()) {
                            $query->where(function (Builder $query): void {
                                $query
                                    ->where('branch_id', BranchAccess::branchId())
                                    ->orWhereHas('bookings', fn (Builder $query): Builder => BranchAccess::scope($query));
                            });
                        }

                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('car_id')
                    ->label(__('Car'))
                    ->relationship('car', 'model', fn (Builder $query): Builder => BranchAccess::scope($query))
                    ->getOptionLabelFromRecordUsing(fn (Car $record): string => "{$record->brand} {$record->model} ({$record->plate_number})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) use ($updateTotal): void {
                        if (! $state) {
                            return;
                        }

                        $car = Car::query()->find($state);

                        if ($car) {
                            $set('daily_rate_agreed', $car->daily_rate);
                            $updateTotal($get, $set);
                        }
                    })
                    ->rule(function ($get, ?Model $record): Closure {
                        return function (string $attribute, $value, Closure $fail) use ($get, $record): void {
                            $pickup = $get('pickup_datetime');
                            $return = $get('return_datetime_planned');

                            if (! $pickup) {
                                return;
                            }

                            if (Booking::hasConflict((int) $value, $pickup, $return, $record?->getKey())) {
                                $fail(__('This car is already booked during the selected dates.'));
                            }
                        };
                    }),
                Select::make('agent_id')
                    ->label(__('Agent'))
                    ->relationship('agent', 'name', fn (Builder $query): Builder => BranchAccess::scope($query))
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => auth()->id())
                    ->disabled(fn (): bool => BranchAccess::isRestricted())
                    ->dehydrated(),
                Select::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name', fn (Builder $query): Builder => BranchAccess::scope($query, 'id'))
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => BranchAccess::branchId())
                    ->disabled(fn (): bool => BranchAccess::isRestricted())
                    ->dehydrated(),
                DateTimePicker::make('pickup_datetime')
                    ->label(__('Pickup date and time'))
                    ->required()
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateTotal)
                    ->disabledDates($getDisabledDates)
                    ->minDate(now()->startOfDay()),
                DateTimePicker::make('return_datetime_planned')
                    ->label(__('Planned return date and time'))
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateTotal)
                    ->disabledDates($getDisabledDates)
                    ->minDate(fn ($get) => $get('pickup_datetime') ? Carbon::parse($get('pickup_datetime')) : now()->startOfDay()),
                DateTimePicker::make('return_datetime_actual')
                    ->label(__('Actual return date and time')),
                TextInput::make('pickup_location')
                    ->label(__('Pickup location')),
                TextInput::make('return_location')
                    ->label(__('Return location')),
                TextInput::make('daily_rate_agreed')
                    ->label(__('Daily rate'))
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateTotal),
                TextInput::make('total_amount')
                    ->label(__('Total amount'))
                    ->numeric(),
                TextInput::make('deposit_amount')
                    ->label(__('Deposit'))
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->label(__('Discount'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated($updateTotal),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'reserved' => __('Reserved'),
                        'active' => __('Active'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ])
                    ->required()
                    ->default('reserved'),
                TextInput::make('pickup_mileage')
                    ->label(__('Pickup mileage'))
                    ->numeric(),
                TextInput::make('return_mileage')
                    ->label(__('Return mileage'))
                    ->numeric(),
                TextInput::make('pickup_fuel_level')
                    ->label(__('Pickup fuel level')),
                TextInput::make('return_fuel_level')
                    ->label(__('Return fuel level')),
                TextInput::make('contract_pdf_path')
                    ->label(__('Contract PDF path')),
            ]);
    }
}
