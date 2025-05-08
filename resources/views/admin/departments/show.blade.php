@extends('layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $department->name }} Department Details</h2>
        <div class="btn-group">
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary">Departments</a>
            <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-outline-primary">Edit</a>
            
            @if($department->projects->count() === 0)
                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Are you sure you want to delete this department?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header h5">Description</div>
                <div class="card-body">
                    {!! nl2br(e($department->description)) ?: '<em>No description provided</em>' !!}
                </div>
            </div>
            
            <div class="card">
                <div class="card-header h5">Projects</div>
                <div class="card-body">
                    @if($projects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Key</th>
                                        <th>Name</th>
                                        <th>Tasks</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                        <tr>
                                            <td>{{ $project->key }}</td>
                                            <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                                            <td>{{ $project->tasks_count }}</td>
                                            <td>{{ $project->created_at->format('d.m.Y H:i') }}</td>
                                            <td>{{ $project->updated_at->format('d.m.Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">No projects in this department yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header h5">Department Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $department->id }}</dd>
                        
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">{{ $department->name }}</dd>
                        
                        <dt class="col-sm-4">Code:</dt>
                        <dd class="col-sm-8">{{ $department->code }}</dd>

                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8">{{ $department->created_at->format('d.m.Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-4">Updated:</dt>
                        <dd class="col-sm-8">{{ $department->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
            
            <!-- Department Members Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Department Members</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        Add Member
                    </button>
                </div>
                <div class="card-body p-0">
                    @if(isset($users) && $users->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($users as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                                    <div>
                                        <form method="POST" action="{{ route('admin.departments.removeUser', [$department, $user]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this user from the department?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center p-3">
                            <p class="mb-0">No members in this department yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">Add Member to {{ $department->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.departments.addUser', $department) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select a user</option>
                            @foreach(\App\Models\User::whereNull('department_id')->orWhere('department_id', '!=', $department->id)->orderBy('name')->get() as $availableUser)
                                <option value="{{ $availableUser->id }}">{{ $availableUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection