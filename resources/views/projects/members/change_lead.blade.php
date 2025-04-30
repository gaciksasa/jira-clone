@extends('layouts.app')

@section('title', 'Change Project Lead - ' . $project->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Project Lead - {{ $project->name }}</h5>
                </div>

                <div class="card-body">
                    <p>The current project lead is <strong>{{ $project->lead->name }}</strong>.</p>
                    
                    <form method="POST" action="{{ route('projects.members.update-lead', $project) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="lead_id" class="form-label">Select New Project Lead</label>
                            <select class="form-select @error('lead_id') is-invalid @enderror" id="lead_id" name="lead_id" required>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ $member->id == old('lead_id', $project->lead_id) ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Only project members can be selected as project lead.</div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Changing the project lead will transfer all project management responsibilities to the selected user.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('projects.members.index', $project) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Change Project Lead</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection