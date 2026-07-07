<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand', 'model', 'year', 'plate_number', 'vin', 'category',
        'color', 'transmission', 'fuel_type', 'seats', 'daily_rate',
        'weekly_rate', 'monthly_rate', 'mileage', 'status', 'branch_id',
        'features', 'insurance_expiry', 'registration_expiry',
        'last_service_date', 'next_service_due'
    ];

    protected $casts = [
        'features' => 'array',
        'insurance_expiry' => 'date',
        'registration_expiry' => 'date',
        'last_service_date' => 'date',
        'next_service_due' => 'date',
    ];

    public function scopeAvailableForPeriod(
        Builder $query,
        CarbonInterface|string $pickup,
        CarbonInterface|string $return,
    ): Builder {
        return $query->whereDoesntHave('bookings', function (Builder $query) use ($pickup, $return): void {
            $query
                ->whereIn('status', ['reserved', 'active', 'overdue'])
                ->where(function (Builder $query) use ($pickup, $return): void {
                    $query->where(function (Builder $query) use ($pickup): void {
                        $query
                            ->whereNull('return_datetime_planned')
                            ->orWhere('return_datetime_planned', '>', $pickup);
                    });

                    $query->where('pickup_datetime', '<', $return);
                });
        });
    }

    public function refreshOperationalStatus(?int $mileage = null): void
    {
        $updates = [];

        if ($mileage !== null) {
            $updates['mileage'] = $mileage;
        }

        if (in_array($this->status, ['maintenance', 'out_of_service'], true)) {
            if ($updates !== []) {
                $this->update($updates);
            }

            return;
        }

        $updates['status'] = match (true) {
            $this->bookings()->whereIn('status', ['active', 'overdue'])->exists() => 'rented',
            $this->bookings()
                ->where('status', 'reserved')
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('return_datetime_planned')
                        ->orWhere('return_datetime_planned', '>', now());
                })
                ->exists() => 'reserved',
            default => 'available',
        };

        $this->update($updates);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function photos()
    {
        return $this->hasMany(CarPhoto::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(CarMaintenanceLog::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(CarStatusHistory::class);
    }
}
