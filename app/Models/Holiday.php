<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'is_recurring',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];
    
    /**
     * Check if a given date is a holiday
     *
     * @param Carbon\Carbon|string $date
     * @return bool
     */
    public static function isHoliday($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        // Format for comparison
        $checkDate = $date->format('Y-m-d');
        $monthDay = $date->format('m-d');
        
        // First check non-recurring holidays (exact date match)
        $exactMatch = self::where('date', $checkDate)
            ->where('is_recurring', false)
            ->exists();
            
        if ($exactMatch) {
            return true;
        }
        
        // Then check recurring holidays with more reliable approach
        $recurringHolidays = self::where('is_recurring', true)->get();
        
        foreach ($recurringHolidays as $holiday) {
            if ($holiday->date->format('m-d') === $monthDay) {
                return true;
            }
        }
        
        return false;
    }
}