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
        
        // Check exact dates for non-recurring holidays
        $exactMatch = self::where('date', $checkDate)
            ->where('is_recurring', false)
            ->exists();
            
        if ($exactMatch) {
            return true;
        }
        
        // Check recurring holidays (only comparing month and day)
        $recurringMatch = self::where('is_recurring', true)
            ->whereRaw("DATE_FORMAT(date, '%m-%d') = ?", [$monthDay])
            ->exists();
            
        return $recurringMatch;
    }
}