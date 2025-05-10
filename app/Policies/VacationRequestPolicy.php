<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class VacationRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the vacation request.
     */
    public function view(User $user, VacationRequest $vacationRequest)
    {
        return $user->id === $vacationRequest->user_id || 
               $user->id === $vacationRequest->approver_id || 
               $user->can('manage users');
    }

    /**
     * Determine whether the user can cancel the vacation request.
     */
    public function cancel(User $user, VacationRequest $vacationRequest)
    {
        // Users can only cancel their own vacation requests if they're still pending
        return $user->id === $vacationRequest->user_id && 
               $vacationRequest->status === 'pending';
    }
}