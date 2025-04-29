<!-- resources/views/projects/sprints/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Sprint - ' . $sprint->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Sprint - {{ $project->name }}</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('projects.sprints.update', [$project, $sprint]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Sprint Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $sprint->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $sprint->start_date ? $sprint->start_date->format('Y-m-d') : '') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $sprint->end_date ? $sprint->end_date->format('Y-m-d') : '') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="planning" {{ old('status', $sprint->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                                <option value="active" {{ old('status', $sprint->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $sprint->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('projects.sprints.show', [$project, $sprint]) }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Sprint</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p>Deleting a sprint will move all associated tasks back to the project backlog.</p>
                    <form method="POST" action="{{ route('projects.sprints.destroy', [$project, $sprint]) }}" onsubmit="return confirm('Are you sure you want to delete this sprint? All tasks will be moved to the project backlog.');">
                        @csrf
                        @method('DELETE')
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">Delete Sprint</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection