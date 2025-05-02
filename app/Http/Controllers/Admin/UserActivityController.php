<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    /**
     * Display a listing of user activities.
     */
    public function index(Request $request)
    {
        $this->authorize('manage users');
        
        $query = UserActivity::with('user');
        
        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by action if provided
        if ($request->has('action') && $request->action) {
            $query->where('action', 'LIKE', '%' . $request->action . '%');
        }
        
        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $activities = $query->latest()->paginate(20);
        
        return view('admin.activities.index', compact('activities'));
    }
}