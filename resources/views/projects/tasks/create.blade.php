@extends('layouts.app')

@section('title', 'Create Task - ' . $project->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create Task - {{ $project->name }}</h5>
                </div>

                <div class="card-body">
                    @if(isset($parentTask))
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle"></i> Creating a subtask for: 
                            <strong>{{ $parentTask->task_number }}: {{ $parentTask->title }}</strong>
                        </div>
                        <input type="hidden" name="parent_id" value="{{ $parentTask->id }}">
                    @else
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Task (Optional)</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">None (Top-level task)</option>
                                @foreach($project->tasks()->whereNull('parent_id')->get() as $potentialParent)
                                    <option value="{{ $potentialParent->id }}" {{ old('parent_id') == $potentialParent->id ? 'selected' : '' }}>
                                        {{ $potentialParent->task_number }}: {{ $potentialParent->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                    <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="task_type_id" class="form-label">Type</label>
                                <select class="form-select @error('task_type_id') is-invalid @enderror" id="task_type_id" name="task_type_id" required>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('task_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('task_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="priority_id" class="form-label">Priority</label>
                                <select class="form-select @error('priority_id') is-invalid @enderror" id="priority_id" name="priority_id" required>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="task_status_id" class="form-label">Status</label>
                                <select class="form-select @error('task_status_id') is-invalid @enderror" id="task_status_id" name="task_status_id" required>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ old('task_status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('task_status_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="assignee_id" class="form-label">Assignee</label>
                                <select class="form-select @error('assignee_id') is-invalid @enderror" id="assignee_id" name="assignee_id">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assignee_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assignee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!--<div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sprint_id" class="form-label">Sprint</label>
                                <select class="form-select @error('sprint_id') is-invalid @enderror" id="sprint_id" name="sprint_id">
                                    <option value="">Backlog (No Sprint)</option>
                                    @foreach($sprints as $sprint)
                                        <option value="{{ $sprint->id }}" {{ old('sprint_id') == $sprint->id ? 'selected' : '' }}>
                                            {{ $sprint->name }} ({{ ucfirst($sprint->status) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('sprint_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="story_points" class="form-label">Story Points</label>
                                <input type="number" class="form-control @error('story_points') is-invalid @enderror" id="story_points" name="story_points" min="1" max="100" value="{{ old('story_points') }}">
                                @error('story_points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>-->

                        <div class="form-group mb-3">
                            <label for="labels">Labels</label>
                            <div class="card">
                                <div class="card-body" style="max-height: 150px; overflow-y: auto;">
                                    @forelse($labels as $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="labels[]" value="{{ $label->id }}" id="label-{{ $label->id }}">
                                            <label class="form-check-label" for="label-{{ $label->id }}">
                                                <span class="badge" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted">No labels available for this project.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection