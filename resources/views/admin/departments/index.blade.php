@extends('layouts.app')

@section('title', 'Department Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Department Management</h2>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">Create Department</a>
    </div>

    <div class="card">
        <div class="card-header h5">Departments</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Projects</th>
                            <th>Members</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr>
                                <td>{{ $department->code }}</td>
                                <td><a href="{{ route('admin.departments.show', $department) }}">{{ $department->name }}</a></td>
                                <td>{{ $department->projects_count }}</td>
                                <td>{{ $department->users_count }}</td>
                                <td>{{ $department->created_at->format('d.m.Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Are you sure you want to delete this department?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" {{ $department->projects_count > 0 ? 'disabled' : '' }}>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No departments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection