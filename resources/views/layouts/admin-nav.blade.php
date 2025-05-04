<div class="card mb-4">
    <div class="card-header">
        Admin Panel
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="{{ route('admin.projects.index') }}" class="d-block {{ request()->routeIs('admin.projects.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-kanban"></i> Projects
                </a>
            </li>
            <li class="list-group-item">
                <a href="{{ route('admin.departments.index') }}" class="d-block {{ request()->routeIs('admin.departments.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-building"></i> Departments
                </a>
            </li>
            <li class="list-group-item">
                <a href="{{ route('admin.users.index') }}" class="d-block {{ request()->routeIs('admin.users.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="list-group-item">
                <a href="{{ route('admin.roles.index') }}" class="d-block {{ request()->routeIs('admin.roles.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-shield-lock"></i> Roles
                </a>
            </li>
            <li class="list-group-item">
                @if(Route::has('admin.activities.index'))
                    <a href="{{ route('admin.activities.index') }}" class="d-block {{ request()->routeIs('admin.activities.*') ? 'fw-bold text-primary' : '' }}">
                        <i class="bi bi-activity"></i> User Activities
                    </a>
                @else
                    <span class="d-block text-muted">
                        <i class="bi bi-activity"></i> User Activities (Route not configured)
                    </span>
                @endif
            </li>
            @if(isset($project))
            <li class="list-group-item">
                <a href="{{ route('projects.statuses.index', $project) }}" class="d-block {{ request()->routeIs('projects.statuses.*') ? 'fw-bold text-primary' : '' }}">
                    <i class="bi bi-kanban"></i> Workflow Statuses
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>