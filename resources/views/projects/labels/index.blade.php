@extends('layouts.app')

@section('title', 'Manage Labels - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $project->name }} - Manage Labels</h2>
            <p class="text-muted mb-0">{{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.tasks.index', $project) }}" class="btn btn-outline-primary">Tasks</a>
            <a href="{{ route('projects.labels.create', $project) }}" class="btn btn-primary">Create Label</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Labels</h5>
        </div>
        <div class="card-body">
            @if($labels->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Tasks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labels as $label)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $label->color }}; min-width: 80px;">
                                            {{ $label->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="color-box me-2" style="width: 20px; height: 20px; background-color: {{ $label->color }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                            <span>{{ $label->color }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $label->tasks->count() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('projects.labels.edit', [$project, $label]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="POST" action="{{ route('projects.labels.destroy', [$project, $label]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this label?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center">
                    <p>No labels defined yet. Click 'Create Label' to add your first label.</p>
                    <a href="{{ route('projects.labels.create', $project) }}" class="btn btn-primary">Create Label</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection