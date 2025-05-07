@extends('layouts.app')

@section('title', 'User Activity Log')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard (Activities)</h1>
    </div>

    <div class="card mb-4">
        <div class="card-header">Filter Activities</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activities.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">All Users</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="action" class="form-label">Action</label>
                    <input type="text" class="form-control" id="action" name="action" value="{{ request('action') }}" placeholder="Search actions...">
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">User Activities</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>{{ $activity->id }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $activity->user) }}">
                                        {{ $activity->user->name }}
                                    </a>
                                </td>
                                <td>{{ $activity->action }}</td>
                                <td>{{ $activity->ip_address }}</td>
                                <td>{{ $activity->created_at->format('d.m.Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No activities found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $activities->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection