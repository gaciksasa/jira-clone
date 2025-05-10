<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class InspectHolidays extends Command
{
    protected $signature = 'holidays:inspect {startDate} {endDate}';
    protected $description = 'Inspect holidays in a given date range';

    public function handle()
    {
        $startDate = Carbon::parse($this->argument('startDate'));
        $endDate = Carbon::parse($this->argument('endDate'));
        
        $this->info("Inspecting holidays from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        
        $period = CarbonPeriod::create($startDate, $endDate);
        $holidaysFound = [];
        
        foreach ($period as $date) {
            if (Holiday::isHoliday($date)) {
                $holidaysFound[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('l'),
                ];
            }
        }
        
        if (empty($holidaysFound)) {
            $this->warn("No holidays found in the given date range.");
        } else {
            $this->info("Holidays found:");
            $this->table(['Date', 'Day'], $holidaysFound);
        }
        
        return 0;
    }
}