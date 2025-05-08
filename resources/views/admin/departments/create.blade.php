@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header h5">Create Department</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.departments.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Department Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Department Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required maxlength="10" placeholder="DEV">
                            <div class="form-text">A short identifier for the department (e.g., DEV). Maximum 10 characters, alphanumeric only.</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Department</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate department code from name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        if (name) {
            // Extract first letter of each word and convert to uppercase
            const code = name.split(/\s+/).map(word => word.charAt(0).toUpperCase()).join('');
            document.getElementById('code').value = code;
        }
    });
</script>
@endpush