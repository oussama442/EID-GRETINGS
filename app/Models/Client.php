<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'full_name', 'phone', 'email', 'national_id_number',
        'driver_license_number', 'driver_license_expiry',
        'id_document_photo', 'license_photo', 'address',
        'date_of_birth', 'notes', 'is_blacklisted', 'blacklist_reason'
    ];

    protected $casts = [
        'is_blacklisted' => 'boolean',
        'driver_license_expiry' => 'date',
        'date_of_birth' => 'date',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
