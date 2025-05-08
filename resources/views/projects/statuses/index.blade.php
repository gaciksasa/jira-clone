@extends('layouts.app')

@section('title', 'Manage Board - ' . $project->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $project->name }} - Manage Board</h1>
            <p class="text-muted mb-0">{{ $project->key }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">Project</a>
            <a href="{{ route('projects.board', $project) }}" class="btn btn-outline-primary">Board</a>
            <a href="{{ route('projects.statuses.create', $project) }}" class="btn btn-primary">Add Column</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Board Columns</h5>
        </div>
        <div class="card-body">
            @if($statuses->isEmpty())
                <div class="alert alert-info">
                    <p class="mb-0">No columns defined yet. Add some columns to define your project workflow.</p>
                </div>
            @else
                <p class="mb-3">Drag and drop to reorder columns. Tasks will flow through these columns from left to right on the board.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="statuses-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Tasks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-statuses">
                            @foreach($statuses as $status)
                                <tr data-status-id="{{ $status->id }}">
                                    <td>{{ $status->order }}</td>
                                    <td>{{ $status->name }}</td>
                                    <td>{{ $status->tasks->count() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('projects.statuses.edit', [$project, $status]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $status->id }}">
                                                Delete
                                            </button>
                                        </div>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $status->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $status->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel{{ $status->id }}">Delete "{{ $status->name }}" Column</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>You are about to delete the "{{ $status->name }}" column which contains {{ $status->tasks->count() }} tasks.</p>
                                                        <p>Please select a column to move these tasks to:</p>
                                                        
                                                        <form id="deleteForm{{ $status->id }}" method="POST" action="{{ route('projects.statuses.destroy', [$project, $status]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            
                                                            <div class="mb-3">
                                                                <label for="target_status_id" class="form-label">Move tasks to:</label>
                                                                <select class="form-select" id="target_status_id" name="target_status_id" required>
                                                                    @foreach($statuses as $targetStatus)
                                                                        @if($targetStatus->id != $status->id)
                                                                            <option value="{{ $targetStatus->id }}">{{ $targetStatus->name }}</option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" form="deleteForm{{ $status->id }}" class="btn btn-danger">Delete and Move Tasks</button>
                                                    </div>
                                                </div>
                                            </div>
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