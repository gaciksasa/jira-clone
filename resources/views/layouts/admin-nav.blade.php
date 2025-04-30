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
        </ul>
    </div>
</div>