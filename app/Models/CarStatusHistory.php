<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = ['car_id', 'status', 'changed_at', 'changed_by', 'notes'];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
