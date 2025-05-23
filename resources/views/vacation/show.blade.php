@extends('layouts.app')

@section('title', 'Vacation Request Details')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vacation Request Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $vacationRequest->type == 'vacation' ? 'bg-primary' : ($vacationRequest->type == 'sick_leave' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $vacationRequest->type)) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Start Date</dt>
                        <dd class="col-sm-8">{{ $vacationRequest->start_date->format('d.m.Y, l') }}</dd>
                        
                        <dt class="col-sm-4">End Date</dt>
                        <dd class="col-sm-8">{{ $vacationRequest->end_date->format('d.m.Y, l') }}</dd>
                        
                        <dt class="col-sm-4">Working Days</dt>
                        <dd class="col-sm-8">{{ format_days($vacationRequest->days_count) }}</dd>
                        
                        <dt class="col-sm-4">Approver</dt>
                        <dd class="col-sm-8">{{ $vacationRequest->approver->name }}</dd>
                        
                        <dt class="col-sm-4">Requested On</dt>
                        <dd class="col-sm-8">{{ $vacationRequest->created_at->format('d.m.Y H:i') }}</dd>
                        
                        @if($vacationRequest->status != 'pending')
                            <dt class="col-sm-4">Responded On</dt>
                            <dd class="col-sm-8">{{ $vacationRequest->responded_at->format('d.m.Y H:i') }}</dd>
                        @endif
                        
                        @if($vacationRequest->comment)
                            <dt class="col-sm-4">Comment</dt>
                            <dd class="col-sm-8">{{ $vacationRequest->comment }}</dd>
                        @endif
                        
                        @if($vacationRequest->response_comment)
                            <dt class="col-sm-4">Response Comment</dt>
                            <dd class="col-sm-8">{{ $vacationRequest->response_comment }}</dd>
                        @endif

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $vacationRequest->status == 'pending' ? 'bg-warning' : ($vacationRequest->status == 'approved' ? 'bg-success' : 'bg-danger') }}">
                                {{ ucfirst($vacationRequest->status) }}
                            </span>
                        </dd>
                    </dl>
                    
                    <div class="mt-4 d-flex justify-content-end">
                        <a href="{{ isset($backToTeam) ? route('vacation.index', ['team' => $backToTeam]) : route('vacation.index') }}" class="btn btn-primary">
                            Back to Calendar
                        </a>
                        
                        @if($vacationRequest->status == 'pending')
                            @if($vacationRequest->user_id == Auth::id())
                                <form method="POST" action="{{ route('vacation.cancel', $vacationRequest) }}" class="ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this request?')">
                                        Cancel Request
                                    </button>
                                </form>
                            @elseif($vacationRequest->approver_id == Auth::id())
                                <!-- Show approve/reject buttons if current user is the approver -->
                                <form method="POST" action="{{ route('admin.vacation-requests.approve', $vacationRequest) }}" class="ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this request?')">
                                        Approve
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('admin.vacation-requests.reject', $vacationRequest) }}" class="ms-2">
                                    @csrf
                                    <div class="d-none">
                                        <input type="text" name="response_comment" value="Request rejected by team lead">
                                    </div>
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this request?')">
                                        Reject
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection