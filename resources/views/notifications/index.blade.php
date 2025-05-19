@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Notifications</h2>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary">Mark All as Read</button>
        </form>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $notification->read_at ? '' : 'bg-light' }}">
                        <div class="me-auto">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    @if(isset($notification->data['message']))
                                        {{ $notification->data['message'] }}
                                    @else
                                        Notification received
                                    @endif
                                </h5>
                                <small>{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            
                            <div class="mt-1">
                                @if(isset($notification->data['task_number']))
                                    <small class="badge bg-info text-dark">Task: {{ $notification->data['task_number'] }}</small>
                                @endif
                                
                                @if(isset($notification->data['project_name']))
                                    <small class="badge bg-secondary">Project: {{ $notification->data['project_name'] }}</small>
                                @endif
                                
                                @if(isset($notification->data['department_name']))
                                    <small class="badge bg-success">Department: {{ $notification->data['department_name'] }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex">
                            @if(isset($notification->data['link']) && 
                                (
                                    (isset($notification->data['task_number'])) || 
                                    (isset($notification->data['project_name']) && !str_contains($notification->data['message'], 'removed from project')) ||
                                    (isset($notification->data['department_name']) && Auth::user()->can('manage users'))
                                )
                              )
                                <a href="{{ $notification->data['link'] }}" class="btn btn-sm btn-primary me-2">
                                    @if(isset($notification->data['task_number']))
                                        View Task
                                    @elseif(isset($notification->data['project_name']))
                                        View Project
                                    @else
                                        View Details
                                    @endif
                                </a>
                            @endif
                            
                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark as Read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-5">
                        <p class="mb-0">No notifications found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection