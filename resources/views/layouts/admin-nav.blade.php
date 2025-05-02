<div class="card mb-4">
    <div class="card-header">
        Admin Panel
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="{{ route('admin.users.index') }}" class="d-block {{ request()->routeIs('admin.users.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-people"></i> User Management
                </a>
            </li>
            <li class="list-group-item">
                <a href="{{ route('admin.roles.index') }}" class="d-block {{ request()->routeIs('admin.roles.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-shield-lock"></i> Role Management
                </a>
            </li>
            <li class="list-group-item">
                @if(Route::has('admin.activities.index'))
                    <a href="{{ route('admin.activities.index') }}" class="d-block {{ request()->routeIs('admin.activities.*') ? 'fw-bold text-primary' : '' }}">
                        <i class="bi bi-activity"></i> User Activity Log
                    </a>
                @else
                    <span class="d-block text-muted">
                        <i class="bi bi-activity"></i> User Activity Log (Route not configured)
                    </span>
                @endif
            </li>
            @if(isset($project))
            <li class="list-group-item">
                <a href="{{ route('projects.statuses.index', $project) }}" class="d-block {{ request()->routeIs('projects.statuses.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-kanban"></i> Manage Workflow Statuses
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>