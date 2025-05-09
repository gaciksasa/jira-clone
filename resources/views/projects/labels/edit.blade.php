@extends('layouts.app')

@section('title', 'Edit Label - ' . $project->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Label - {{ $project->name }}</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('projects.labels.update', [$project, $label]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Label Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $label->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', $label->color) }}">
                                <input type="text" class="form-control @error('color') is-invalid @enderror" id="colorHex" value="{{ old('color', $label->color) }}" readonly>
                            </div>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div>
                                <span class="badge" id="labelPreview" style="background-color: {{ old('color', $label->color) }}">
                                    {{ old('name', $label->name) }}
                                </span>
                            </div>
                        </div>

                        @if($label->tasks->count() > 0)
                            <div class="alert alert-info">
                                <p class="mb-0">This label is currently used by {{ $label->tasks->count() }} task(s). Updating it will affect these tasks.</p>
                            </div>
                        @endif

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('projects.labels.index', $project) }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Label</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    @if($label->tasks->count() > 0)
                        <p>You cannot delete this label because it is used by {{ $label->tasks->count() }} task(s).</p>
                        <button type="button" class="btn btn-danger" disabled>Delete Label</button>
                    @else
                        <p>Deleting this label cannot be undone.</p>
                        <form method="POST" action="{{ route('projects.labels.destroy', [$project, $label]) }}" onsubmit="return confirm('Are you sure you want to delete this label? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger">Delete Label</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorInput = document.getElementById('color');
        const colorHexInput = document.getElementById('colorHex');
        const nameInput = document.getElementById('name');
        const labelPreview = document.getElementById('labelPreview');
        
        // Update the hex input and preview when color changes
        colorInput.addEventListener('input', function() {
            colorHexInput.value = this.value;
            labelPreview.style.backgroundColor = this.value;
        });
        
        // Update the preview text when name changes
        nameInput.addEventListener('input', function() {
            labelPreview.textContent = this.value || 'Label Preview';
        });
    });
</script>
@endpush
@endsection