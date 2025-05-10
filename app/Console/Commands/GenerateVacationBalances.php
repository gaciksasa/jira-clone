<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserVacationBalance;
use App\Models\VacationSetting;
use Illuminate\Console\Command;

class GenerateVacationBalances extends Command
{
    protected $signature = 'vacation:generate-balances {year?}';
    protected $description = 'Generate vacation balances for all users for specified year or current year';

    public function handle()
    {
        $year = $this->argument('year') ?? date('Y');
        $settings = VacationSetting::first();
        
        if (!$settings) {
            $this->error('Vacation settings not found. Please configure vacation settings first.');
            return 1;
        }
        
        $users = User::all();
        $this->info('Generating vacation balances for ' . $users->count() . ' users for year ' . $year);
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            UserVacationBalance::updateOrCreate(
                ['user_id' => $user->id, 'year' => $year],
                ['total_days' => $settings->default_days_per_year, 'used_days' => 0]
            );
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info('Vacation balances have been generated successfully!');
        
        return 0;
    }
}