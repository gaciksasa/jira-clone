@extends('layouts.app')

@section('title', 'Accept Project Invitation')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Project Invitation</h5>
                </div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-4 mb-3">
                            <i class="bi bi-envelope-check text-success"></i>
                        </div>
                        <h4>You've been invited to join "{{ $project->name }}"</h4>
                        <p class="text-muted">Invited by {{ $inviter->name }}</p>
                    </div>

                    <div class="alert alert-info">
                        <p class="mb-0">Project description: {{ $project->description ?: 'No description provided' }}</p>
                    </div>

                    @guest
                        <div class="alert alert-warning">
                            <h5>You need to log in or register first</h5>
                            <p>Please log in or create an account to join this project.</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('login') }}?redirect_to={{ urlencode(route('invitation.accept', $token)) }}" class="btn btn-primary">
                                    Log In
                                </a>
                                <a href="{{ route('register') }}?redirect_to={{ urlencode(route('invitation.accept', $token)) }}" class="btn btn-outline-primary">
                                    Register
                                </a>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('invitation.accept.confirm', $token) }}">
                            @csrf
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    Accept Invitation & Join Project
                                </button>
                            </div>
                        </form>
                    @endguest

                    <div class="mt-4 text-center">
                        <p class="text-muted small">This invitation expires on {{ $invitation->expires_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection