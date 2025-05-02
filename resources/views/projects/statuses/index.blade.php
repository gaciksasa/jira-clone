@extends('layouts.app')

@section('title', 'Manage Statuses - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $project->name }} - Manage Statuses</h1>
            <p class="text-muted mb-0">{{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Back to Project</a>
            <a href="{{ route('projects.statuses.create', $project) }}" class="btn btn-primary">Add Status</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Workflow Statuses</h5>
        </div>
        <div class="card-body">
            @if($statuses->isEmpty())
                <div class="alert alert-info">
                    <p class="mb-0">No statuses defined yet. Add some statuses to define your project workflow.</p>
                </div>
            @else
                <p class="mb-3">Drag and drop to reorder statuses. Tasks will progress through these statuses from left to right on the board.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="statuses-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Name</th>
                                <th width="15%">Color</th>
                                <th width="15%">Tasks</th>
                                <th width="30%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-statuses">
                            @foreach($statuses as $status)
                                <tr data-status-id="{{ $status->id }}">
                                    <td>{{ $status->order }}</td>
                                    <td>{{ $status->name }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $status->color }}; padding: 10px 15px;">
                                            &nbsp;
                                        </span>
                                    </td>
                                    <td>{{ $status->tasks->count() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('projects.statuses.edit', [$project, $status]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="POST" action="{{ route('projects.statuses.destroy', [$project, $status]) }}" onsubmit="return confirm('Are you sure you want to delete this status?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ $status->tasks->count() > 0 ? 'disabled' : '' }}>Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sortable-statuses');
        
        if (el) {
            new Sortable(el, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: function() {
                    const statuses = [];
                    document.querySelectorAll('#sortable-statuses tr').forEach(row => {
                        statuses.push(row.getAttribute('data-status-id'));
                    });
                    
                    // Send the new order to the server
                    fetch('{{ route('projects.statuses.reorder', $project) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            statuses: statuses
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the order numbers in the table
                            document.querySelectorAll('#sortable-statuses tr').forEach((row, index) => {
                                row.cells[0].textContent = index + 1;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
        }
    });
</script>
@endpush