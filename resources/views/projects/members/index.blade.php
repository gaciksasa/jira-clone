@extends('layouts.app')

@section('title', 'Manage Project Members - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $project->name }} - Manage Members</h2>
            <p class="text-muted mb-0">{{ $project->key }} | Project Lead: {{ $project->lead->name }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.members.edit-lead', $project) }}" class="btn btn-outline-primary">Change Project Lead</a>
            <a href="{{ route('vacation.index') }}?team={{ $project->id }}" class="btn btn-outline-primary">Team Time Off Calendar</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Current Members</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                    <tr>
                                        <td>
                                            <a href="{{ route('projects.members.show', [$project, $member]) }}">
                                                {{ $member->name }}
                                            </a>
                                        </td>
                                        <td>{{ $member->email }}</td>
                                        <td>
                                            @if($member->id == $project->lead_id)
                                                <span class="badge bg-primary">Project Lead</span>
                                            @else
                                                <span class="badge bg-secondary">Member</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($member->id != $project->lead_id && $member->id != Auth::id())
                                                <form method="POST" action="{{ route('projects.members.remove', [$project, $member]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this member from the project?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No members found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($members->count() > 0)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> The project lead and yourself cannot be removed from the project.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add Existing Users</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('projects.members.update', $project) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Hidden inputs for current members to preserve them -->
                        @foreach($members as $member)
                            <input type="hidden" name="members[]" value="{{ $member->id }}">
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label">Select Users to Add</label>
                            <div class="card">
                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                    @php $hasAvailableUsers = false; @endphp
                                    @foreach($users as $user)
                                        @if(!$members->contains($user->id) && $user->is_active)
                                            @php $hasAvailableUsers = true; @endphp
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="members[]" value="{{ $user->id }}" id="user-{{ $user->id }}">
                                                <label class="form-check-label" for="user-{{ $user->id }}">
                                                    {{ $user->name }}
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                    
                                    @if(!$hasAvailableUsers)
                                        <p class="text-muted mb-0">All active users are already members of this project.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" {{ !$hasAvailableUsers ? 'disabled' : '' }}>Add Selected Users</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invite User by Email</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('projects.members.invite', $project) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="invite_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('invite_email') is-invalid @enderror" id="invite_email" name="invite_email" required placeholder="Enter user email">
                            @error('invite_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope"></i> Send Invitation
                            </button>
                        </div>
                        
                        <div class="mt-2 small text-muted">
                            <p class="mb-0">If the user is already registered, they will be added to the project immediately.</p>
                            <p class="mb-0">If not, an invitation will be sent to create an account and join the project.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection