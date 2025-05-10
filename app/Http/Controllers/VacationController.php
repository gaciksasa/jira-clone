<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\User;
use App\Models\VacationRequest;
use App\Models\UserVacationBalance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VacationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get current year balance
        $currentYear = date('Y');
        $balance = UserVacationBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => $currentYear],
            ['total_days' => 20, 'used_days' => 0, 'carryover_days' => 0]
        );
        
        // Get user's vacation requests
        $requests = VacationRequest::where('user_id', $user->id)
                                  ->orderBy('created_at', 'desc')
                                  ->get();
        
        // Get approved vacation requests for calendar
        $approvedRequests = VacationRequest::where('status', 'approved')
                                          ->get();
        
        // Get company holidays
        $holidays = Holiday::all();
        
        // Get list of possible approvers
        $approvers = User::where('id', '!=', $user->id)
                         ->whereHas('roles', function($query) {
                             $query->where('name', 'admin')
                                   ->orWhere('name', 'project_manager');
                         })
                         ->get();
        
        return view('vacation.index', compact(
            'user', 
            'balance', 
            'requests', 
            'approvedRequests',
            'holidays',
            'approvers'
        ));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'approver_id' => 'required|exists:users,id',
            'type' => 'required|in:vacation,sick_leave,personal',
            'comment' => 'nullable|string|max:255'
        ]);
        
        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        // Calculate business days (excluding weekends)
        $days = $this->calculateBusinessDays($startDate, $endDate);
        
        // Check if user has enough days
        $balance = $user->currentYearBalance;
        if ($balance->remaining_days < $days && $request->type == 'vacation') {
            return redirect()->back()
                ->with('error', 'You don\'t have enough vacation days left.');
        }
        
        // Create vacation request
        VacationRequest::create([
            'user_id' => $user->id,
            'approver_id' => $request->approver_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => $days,
            'type' => $request->type,
            'comment' => $request->comment,
            'status' => 'pending'
        ]);
        
        return redirect()->route('vacation.index')
            ->with('success', 'Vacation request submitted successfully.');
    }

    public function show(VacationRequest $vacationRequest)
    {
        $this->authorize('view', $vacationRequest);
        
        return view('vacation.show', compact('vacationRequest'));
    }

    public function cancel(VacationRequest $vacationRequest)
    {
        $this->authorize('cancel', $vacationRequest);
        
        if ($vacationRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending requests can be cancelled.');
        }
        
        $vacationRequest->delete();
        
        return redirect()->route('vacation.index')
            ->with('success', 'Vacation request cancelled successfully.');
    }

    private function calculateBusinessDays($startDate, $endDate)
    {
        $days = 0;
        $period = CarbonPeriod::create($startDate, $endDate);
        
        // Get holidays
        $holidays = Holiday::all()->map(function($holiday) {
            return $holiday->date->format('Y-m-d');
        })->toArray();
        
        // Check each day
        foreach ($period as $date) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Skip holidays
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }
            
            // Count this as a business day
            $days++;
        }
        
        return $days;
    }
}