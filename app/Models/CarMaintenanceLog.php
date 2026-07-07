<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarMaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id', 'type', 'description', 'cost', 'date',
        'performed_by', 'odometer', 'attachments'
    ];

    protected $casts = [
        'date' => 'date',
        'attachments' => 'array',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
