<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'customer_name', 'phone', 'license_plate', 'service',
        'preferred_date', 'preferred_time', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return ['preferred_date' => 'date'];
    }
}
