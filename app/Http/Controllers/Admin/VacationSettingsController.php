<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VacationRequest;
use App\Models\VacationSetting;
use App\Models\UserVacationBalance;
use App\Traits\LogsUserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VacationSettingsController extends Controller
{
    use LogsUserActivity;

    public function index()
    {
        // Get or create vacation settings
        $settings = VacationSetting::first();
        
        if (!$settings) {
            // Create default settings if none exist
            $settings = VacationSetting::create([
                'default_days_per_year' => 20,
                'allow_carryover' => true,
                'max_carryover_days' => 5
            ]);
        }
        
        // Get pending requests
        $pendingRequests = VacationRequest::where('status', 'pending')
                                        ->with(['user', 'approver'])
                                        ->orderBy('created_at')
                                        ->get();
        
        return view('admin.vacation-settings.index', compact('settings', 'pendingRequests'));
    }

    public function updateSettings(Request $request)
    {
        $this->validate($request, [
            'default_days_per_year' => 'required|integer|min:0',
            'max_carryover_days' => 'required|integer|min:0'
        ]);
        
        // Make sure allow_carryover is properly handled for checkboxes
        $allowCarryover = $request->has('allow_carryover');
        
        $settings = VacationSetting::first();
        
        if ($settings) {
            $settings->update([
                'default_days_per_year' => $request->default_days_per_year,
                'allow_carryover' => $allowCarryover,
                'max_carryover_days' => $request->max_carryover_days
            ]);
        } else {
            VacationSetting::create([
                'default_days_per_year' => $request->default_days_per_year,
                'allow_carryover' => $allowCarryover, 
                'max_carryover_days' => $request->max_carryover_days
            ]);
        }
        
        $this->logUserActivity('Updated vacation settings');
        
        return redirect()->route('admin.vacation-settings.index')
            ->with('success', 'Vacation settings updated successfully.');
    }

    public function approve(VacationRequest $vacationRequest)
    {
        $vacationRequest->update([
            'status' => 'approved',
            'responded_at' => now(),
            'response_comment' => request('response_comment')
        ]);
        
        // If vacation, update user balance
        if ($vacationRequest->type == 'vacation') {
            $balance = UserVacationBalance::where('user_id', $vacationRequest->user_id)
                                         ->where('year', Carbon::parse($vacationRequest->start_date)->year)
                                         ->first();
            
            if ($balance) {
                $balance->update([
                    'used_days' => $balance->used_days + $vacationRequest->days_count
                ]);
            }
        }
        
        $this->logUserActivity('Approved vacation request for user ID: ' . $vacationRequest->user_id);
        
        return redirect()->back()->with('success', 'Vacation request approved.');
    }

    public function reject(VacationRequest $vacationRequest)
    {
        $vacationRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
            'response_comment' => request('response_comment')
        ]);
        
        $this->logUserActivity('Rejected vacation request for user ID: ' . $vacationRequest->user_id);
        
        return redirect()->back()->with('success', 'Vacation request rejected.');
    }

    public function report()
    {
        $currentYear = date('Y');
        $users = User::with(['vacationBalances' => function($query) use ($currentYear) {
            $query->where('year', $currentYear);
        }])->get();
        
        // Calculate used per month
        $monthlyUsage = [];
        $months = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $month = Carbon::createFromDate($currentYear, $i, 1);
            $months[$i] = $month->format('F');
            
            // Get vacation days used in this month
            $monthStart = $month->startOfMonth()->format('Y-m-d');
            $monthEnd = $month->endOfMonth()->format('Y-m-d');
            
            $requests = VacationRequest::where('status', 'approved')
                                      ->where('type', 'vacation')
                                      ->where(function($query) use ($monthStart, $monthEnd) {
                                          $query->whereBetween('start_date', [$monthStart, $monthEnd])
                                                ->orWhereBetween('end_date', [$monthStart, $monthEnd]);
                                      })
                                      ->get();
            
            foreach ($users as $user) {
                $userRequests = $requests->where('user_id', $user->id);
                $days = 0;
                
                foreach ($userRequests as $request) {
                    // Calculate only days within the month
                    $days += $this->calculateDaysInMonth(
                        $request->start_date, 
                        $request->end_date, 
                        $month->month, 
                        $month->year
                    );
                }
                
                $monthlyUsage[$user->id][$i] = $days;
            }
        }
        
        return view('admin.vacation-settings.report', compact('users', 'months', 'monthlyUsage', 'currentYear'));
    }

    private function calculateDaysInMonth($startDate, $endDate, $month, $year)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Adjust dates if they fall outside the month
        if ($startDate->lt($monthStart)) {
            $startDate = $monthStart;
        }
        
        if ($endDate->gt($monthEnd)) {
            $endDate = $monthEnd;
        }
        
        // Count business days
        $days = 0;
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }
        
        return $days;
    }

    public function recalculateBalances()
    {
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        
        // Get or create settings if they don't exist
        $settings = VacationSetting::first();
        if (!$settings) {
            $settings = VacationSetting::create([
                'default_days_per_year' => 20,
                'allow_carryover' => true,
                'max_carryover_days' => 5
            ]);
        }
        
        try {
            $users = User::all();
            
            foreach ($users as $user) {
                // Get last year's balance if exists
                $lastYearBalance = UserVacationBalance::where('user_id', $user->id)
                                                ->where('year', $lastYear)
                                                ->first();
                
                // Calculate carryover days
                $carryoverDays = 0;
                
                if ($lastYearBalance && $settings->allow_carryover) {
                    $unusedDays = $lastYearBalance->total_days - $lastYearBalance->used_days;
                    $carryoverDays = min($unusedDays, $settings->max_carryover_days);
                }
                
                // Create or update current year balance
                UserVacationBalance::updateOrCreate(
                    ['user_id' => $user->id, 'year' => $currentYear],
                    [
                        'total_days' => $settings->default_days_per_year + $carryoverDays,
                        'carryover_days' => $carryoverDays
                    ]
                );
            }
            
            $this->logUserActivity('Recalculated vacation balances for all users');
            
            return redirect()->back()->with('success', 'Vacation balances recalculated successfully.');
        } catch (\Exception $e) {
            // Log the specific exception for debugging
            \Log::error('Error recalculating balances: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Error recalculating balances: ' . $e->getMessage());
        }
    }
}