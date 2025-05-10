<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVacationBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'total_days',
        'used_days',
        'carryover_days',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingDaysAttribute()
    {
        $remaining = $this->total_days - $this->used_days;
        return $remaining == floor($remaining) ? (int)$remaining : $remaining;
    }
}