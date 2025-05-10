<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'default_days_per_year',
        'allow_carryover',
        'max_carryover_days',
    ];

    protected $casts = [
        'allow_carryover' => 'boolean',
    ];
}