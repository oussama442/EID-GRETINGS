<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingConditionPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'type', 'photo_path', 'notes'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
