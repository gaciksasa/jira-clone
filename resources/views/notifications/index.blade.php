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
                                <h5 class="mb-1">{{ $notification->data['message'] }}</h5>
                                <small>{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">
                                Task <strong>{{ $notification->data['task_number'] }}</strong>: {{ $notification->data['task_title'] }}
                            </p>
                            <small>Project: {{ $notification->data['project_name'] }}</small>
                        </div>
                        <div class="d-flex">
                            <a href="{{ $notification->data['link'] }}" class="btn btn-sm btn-primary me-2">View Task</a>
                            
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