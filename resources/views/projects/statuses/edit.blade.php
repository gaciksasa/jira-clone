@extends('layouts.app')

@section('title', 'Edit Board Column - ' . $project->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Board Column - {{ $project->name }}</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('projects.statuses.update', [$project, $taskStatus]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Column Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $taskStatus->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!--<div class="mb-3">
                            <label for="color" class="form-label">Column Color</label>
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', $taskStatus->color) }}">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>-->

                        @if($taskStatus->tasks->count() > 0)
                            <div class="alert alert-info">
                                <p class="mb-0">This column is currently used by {{ $taskStatus->tasks->count() }} task(s). Updating it will affect these tasks.</p>
                            </div>
                        @endif

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('projects.statuses.index', $project) }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Column</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection