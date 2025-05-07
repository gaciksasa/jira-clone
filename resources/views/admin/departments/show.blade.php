@extends('layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Department Details</h1>
        <div class="btn-group">
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary">Back to Departments</a>
            <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-outline-secondary">Edit</a>
            
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
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Department Information</div>
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
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Description</div>
                <div class="card-body">
                    {!! nl2br(e($department->description)) ?: '<em>No description provided</em>' !!}
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Projects</div>
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
    </div>
</div>
@endsection