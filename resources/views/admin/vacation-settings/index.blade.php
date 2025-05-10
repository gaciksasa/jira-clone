@extends('layouts.app')

@section('title', 'Vacation Settings')

@section('content')
<div class="container">
    <h2 class="mb-4">Vacation Settings</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Global Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.vacation-settings.update') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="default_days_per_year" class="form-label">Default Vacation Days Per Year</label>
                            <input type="number" class="form-control" id="default_days_per_year" name="default_days_per_year" value="{{ $settings->default_days_per_year ?? 20 }}" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="allow_carryover" name="allow_carryover" value="1" {{ ($settings->allow_carryover ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_carryover">Allow Carryover</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_carryover_days" class="form-label">Maximum Carryover Days</label>
                            <input type="number" class="form-control" id="max_carryover_days" name="max_carryover_days" value="{{ $settings->max_carryover_days ?? 5 }}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Yearly Actions</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.vacation-recalculate') }}">
                        @csrf
                        <div class="mb-3">
                            <p>Recalculate vacation balances for all users. This will update their total days based on the default settings and carryover rules.</p>
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle"></i> This action will adjust all user balances for the current year.
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to recalculate all vacation balances?')">
                                Recalculate Balances
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pending Vacation Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingRequests as $request)
                                    <tr>
                                        <td>{{ $request->user->name }}</td>
                                        <td>
                                            <span class="badge {{ $request->type == 'vacation' ? 'bg-primary' : ($request->type == 'sick_leave' ? 'bg-danger' : 'bg-warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                        <td>{{ number_format($request->days_count) }}</td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveModal{{ $request->id }}">
                                                Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $request->id }}">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Approve Vacation Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.vacation-requests.approve', $request) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Approve vacation request for <strong>{{ $request->user->name }}</strong> for {{ $request->days_count }} days.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label for="response_comment" class="form-label">Comment (Optional)</label>
                                                            <textarea class="form-control" id="response_comment" name="response_comment" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Approve Request</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Vacation Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.vacation-requests.reject', $request) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Reject vacation request for <strong>{{ $request->user->name }}</strong>.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label for="response_comment" class="form-label">Reason for Rejection</label>
                                                            <textarea class="form-control" id="response_comment" name="response_comment" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject Request</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No pending vacation requests</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('admin.vacation-report') }}" class="btn btn-primary">
                    View Vacation Reports
                </a>
                <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-primary">
                    Manage Holidays
                </a>
            </div>
        </div>
    </div>
</div>
@endsection