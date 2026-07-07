<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'related_type', 'related_id', 'due_date', 'resolved', 'assigned_to'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'resolved' => 'boolean',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function related()
    {
        return $this->morphTo();
    }
}
