<?php

namespace App\Console\Commands;

use App\Models\VacationSetting;
use Illuminate\Console\Command;

class EnsureVacationSettings extends Command
{
    protected $signature = 'vacation:ensure-settings';
    protected $description = 'Ensure vacation settings exist';

    public function handle()
    {
        if (!VacationSetting::first()) {
            VacationSetting::create([
                'default_days_per_year' => 20,
                'allow_carryover' => true,
                'max_carryover_days' => 5
            ]);
            $this->info('Default vacation settings created.');
        } else {
            $this->info('Vacation settings already exist.');
        }
        
        return 0;
    }
}