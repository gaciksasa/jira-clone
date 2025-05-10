@extends('layouts.app')

@section('title', 'Manage Holidays')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Company Holidays</h2>
        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
            Add New Holiday
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Company Holidays</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Recurring</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            <tr>
                                <td>{{ $holiday->name }}</td>
                                <td>{{ $holiday->date->format('M d, Y') }}</td>
                                <td>
                                    @if($holiday->is_recurring)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this holiday?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No holidays configured yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection