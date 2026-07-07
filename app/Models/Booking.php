<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function (Booking $booking) {
            if ($booking->status === 'reserved' && $booking->pickup_datetime && \Carbon\Carbon::parse($booking->pickup_datetime)->isPast()) {
                $booking->status = 'active';
            }
        });

        static::saved(function (Booking $booking) {
            if ($booking->car) {
                if ($booking->status === 'active') {
                    $booking->car->update(['status' => 'rented']);
                } elseif (in_array($booking->status, ['completed', 'cancelled'])) {
                    $booking->car->refreshOperationalStatus();
                }
            }
        });
    }

    protected $fillable = [
        'reference_number', 'client_id', 'car_id', 'agent_id', 'branch_id',
        'pickup_datetime', 'return_datetime_planned', 'return_datetime_actual',
        'pickup_location', 'return_location', 'daily_rate_agreed', 'total_amount',
        'deposit_amount', 'discount', 'status', 'pickup_mileage', 'return_mileage',
        'pickup_fuel_level', 'return_fuel_level', 'contract_pdf_path'
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime_planned' => 'datetime',
        'return_datetime_actual' => 'datetime',
    ];

    public function scopeOverlappingPeriod(
        Builder $query,
        int $carId,
        CarbonInterface|string $pickup,
        CarbonInterface|string|null $return = null,
        ?int $ignoreBookingId = null,
    ): Builder {
        return $query
            ->where('car_id', $carId)
            ->whereIn('status', ['reserved', 'active', 'overdue'])
            ->when($ignoreBookingId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreBookingId))
            ->where(function (Builder $query) use ($pickup, $return): void {
                $query->where(function (Builder $query) use ($pickup): void {
                    $query
                        ->whereNull('return_datetime_planned')
                        ->orWhere('return_datetime_planned', '>', $pickup);
                });

                if ($return) {
                    $query->where('pickup_datetime', '<', $return);
                }
            });
    }

    public static function hasConflict(
        int $carId,
        CarbonInterface|string $pickup,
        CarbonInterface|string|null $return = null,
        ?int $ignoreBookingId = null,
    ): bool {
        return self::query()
            ->overlappingPeriod($carId, $pickup, $return, $ignoreBookingId)
            ->exists();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function conditionPhotos()
    {
        return $this->hasMany(BookingConditionPhoto::class);
    }
}
