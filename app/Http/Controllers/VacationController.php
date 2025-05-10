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
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if viewing team calendar
        $viewingTeam = false;
        $team = null;
        $teamMemberIds = [];
        
        if ($request->has('team')) {
            $projectId = $request->team;
            $project = \App\Models\Project::findOrFail($projectId);
            
            // Check if user is lead or has permission
            if ($project->lead_id === $user->id || $user->can('manage users')) {
                $viewingTeam = true;
                $team = $project;
                
                // Get team members' IDs
                $teamMemberIds = $project->members->pluck('id')->toArray();
            } else {
                // Not authorized to view team calendar
                return redirect()->route('vacation.index')
                    ->with('error', 'You do not have permission to view team calendar.');
            }
        }
        
        // Get current year balance for the user (only needed for personal view)
        $currentYear = date('Y');
        $balance = UserVacationBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => $currentYear],
            ['total_days' => 20, 'used_days' => 0, 'carryover_days' => 0]
        );
        
        // Get vacation requests - different logic for team vs personal view
        $requestsQuery = VacationRequest::query();
        
        if ($viewingTeam) {
            // For team view, only show pending requests from team members
            $requestsQuery->whereIn('user_id', $teamMemberIds)
                        ->where('status', 'pending')
                        ->with('user', 'approver'); // Eager load relationships
        } else {
            // For personal view, show all of user's requests
            $requestsQuery->where('user_id', $user->id)
                        ->with('approver'); // Eager load approver relationship
        }
        
        $requests = $requestsQuery->orderBy('created_at', 'desc')->get();
        
        // Get approved requests for calendar display
        $approvedRequestsQuery = VacationRequest::where('status', 'approved');
        
        if ($viewingTeam) {
            // Show team's approved requests
            $approvedRequestsQuery->whereIn('user_id', $teamMemberIds)
                                ->with('user'); // Eager load user relationship
        } else {
            // Show only user's approved requests
            $approvedRequestsQuery->where('user_id', $user->id);
        }
        
        $approvedRequests = $approvedRequestsQuery->get();
        
        // Get company holidays
        $holidays = Holiday::all();
        
        // Prepare holiday events for the calendar
        $holidayEvents = [];
        $currentYear = date('Y');
        $startMonth = Carbon::now()->startOfMonth()->subMonths(1);
        $endMonth = Carbon::now()->endOfMonth()->addMonths(6);
        
        // Process all holidays in the displayed range
        foreach ($holidays as $holiday) {
            if ($holiday->is_recurring) {
                // For recurring holidays, use the current year
                $date = Carbon::createFromDate(
                    $currentYear, 
                    $holiday->date->format('m'), 
                    $holiday->date->format('d')
                );
                
                if ($date->between($startMonth, $endMonth)) {
                    $holidayEvents[] = [
                        'title' => $holiday->name . ' (Holiday)',
                        'start' => $date->format('Y-m-d'),
                        'className' => 'fc-event-holiday',
                        'display' => 'block'
                    ];
                }
            } else {
                // For non-recurring holidays, use the original date
                if ($holiday->date->between($startMonth, $endMonth)) {
                    $holidayEvents[] = [
                        'title' => $holiday->name . ' (Holiday)',
                        'start' => $holiday->date->format('Y-m-d'),
                        'className' => 'fc-event-holiday',
                        'display' => 'block'
                    ];
                }
            }
        }
        
        // Get list of possible approvers (for request form)
        $approvers = User::where('id', '!=', $user->id)
                        ->whereHas('roles', function($query) {
                            $query->where('name', 'admin')
                                ->orWhere('name', 'project_manager');
                        })
                        ->get();
        
        // For pending approvals that need the user's attention (notification badge)
        $pendingApprovalsCount = VacationRequest::where('approver_id', $user->id)
                                            ->where('status', 'pending')
                                            ->count();
        
        return view('vacation.index', compact(
            'user', 
            'balance', 
            'requests', 
            'approvedRequests',
            'holidays',
            'holidayEvents',
            'approvers',
            'viewingTeam',
            'team',
            'pendingApprovalsCount'
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

    public function show(Request $request, VacationRequest $vacationRequest)
    {
        $this->authorize('view', $vacationRequest);
        
        // Check if coming from team view
        $backToTeam = $request->get('team');
        
        return view('vacation.show', compact('vacationRequest', 'backToTeam'));
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
        
        // Check each day
        foreach ($period as $date) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Skip holidays
            if (Holiday::isHoliday($date)) {
                continue;
            }
            
            // Count this as a business day
            $days++;
        }
        
        return $days;
    }
}