<?php

namespace App\Traits;

use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsUserActivity
{
    /**
     * Log a user activity.
     */
    protected function logUserActivity($action)
    {
        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }
    }
}