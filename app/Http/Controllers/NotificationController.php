<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(15);
        
        return view('notifications.index', compact('notifications'));
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notification marked as read.');
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
    
    /**
     * Get unread notifications for AJAX requests.
     */
    public function getUnreadNotifications()
    {
        // Log request for debugging
        \Log::info('Notification request received');
        
        // Only respond to AJAX requests
        if (!request()->ajax() && !request()->wantsJson()) {
            return redirect()->route('home');
        }

        try {
            $unreadNotifications = Auth::user()->unreadNotifications()->latest()->take(5)->get();
            $count = Auth::user()->unreadNotifications()->count();
            
            \Log::info('Returning notifications', [
                'count' => $count,
                'notifications' => $unreadNotifications->toArray()
            ]);
            
            return response()->json([
                'notifications' => $unreadNotifications,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}