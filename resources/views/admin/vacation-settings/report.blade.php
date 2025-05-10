@extends('layouts.app')

@section('title', 'Vacation Report')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Vacation Report {{ $currentYear }}</h2>
        <a href="{{ route('admin.vacation-settings.index') }}" class="btn btn-outline-primary">
            Back to Settings
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Annual Overview</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Total Days</th>
                            <th>Used Days</th>
                            <th>Remaining</th>
                            <th>Carryover</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @php
                                $balance = $user->vacationBalances->first();
                            @endphp
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $balance ? number_format($balance->total_days) : 'N/A' }}</td>
                                <td>{{ $balance ? number_format($balance->used_days) : 'N/A' }}</td>
                                <td>
                                    @if($balance)
                                        <span class="{{ ($balance->total_days - $balance->used_days) < 5 ? 'text-danger' : '' }}">
                                            {{ number_format($balance->total_days - $balance->used_days) }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $balance ? number_format($balance->carryover_days) : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Monthly Usage</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            @foreach($months as $monthNum => $monthName)
                                <th>{{ substr($monthName, 0, 3) }}</th>
                            @endforeach
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                @php $userTotal = 0; @endphp
                                @foreach($months as $monthNum => $monthName)
                                    @php 
                                        $days = $monthlyUsage[$user->id][$monthNum] ?? 0;
                                        $userTotal += $days;
                                    @endphp
                                    <td>{{ $days > 0 ? $days : '-' }}</td>
                                @endforeach
                                <td><strong>{{ $userTotal }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Chart visualization could be added here -->
    
</div>
@endsection