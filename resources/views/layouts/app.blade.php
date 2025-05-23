<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LMB Dashboard- @yield('title')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <!--<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.5/dist/litera/bootstrap.min.css" rel="stylesheet">-->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.5/dist/spacelab/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .bg-admin {
            /* background-color: lightgray;*/
        }

        .bg-light.bg-info {
            background-color: #17a2b8 !important;
        }

        /* Styles for task cards */
        .task-card {
            cursor: grab;
            margin-bottom: 10px;
            transition: all 0.2s ease;
            position: relative;
        }

        .task-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .task-card.non-draggable {
            cursor: not-allowed !important;
            opacity: 0.85;
            background-color: #f9f9f9;
        }

        .task-card.non-draggable:hover {
            box-shadow: none;
        }

        .task-card.non-draggable:after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            /* This ensures that the overlay prevents interactions while still allowing the link to work */
        }

        .task-card.non-draggable .stretched-link {
            z-index: 2; /* Make sure the link is above the overlay */
        }

        /* Badge to indicate non-draggable status */
        .task-card.non-draggable:before {
            content: "🔒";
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 12px;
            z-index: 3;
        }

        /* Task attributes styling */
        .task-type-icon {
            width: 16px;
            height: 16px;
            display: inline-block;
            margin-right: 5px;
            border-radius: 50%;
        }

        .priority-label {
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
        }

        /* Sortable ghost element styling */
        .sortable-ghost {
            opacity: 0.5;
            background-color: #f1f1f1;
        }
        .kanban-column {
            min-height: 300px;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
        }
        .task-type-icon {
            width: 16px;
            height: 16px;
            display: inline-block;
            margin-right: 5px;
        }
        .priority-label {
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 3px;
        }
        a .badge {
            transition: opacity 0.2s;
        }

        a .badge:hover {
            opacity: 0.8;
            text-decoration: none;
        }
        .notification-dropdown {
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .notification-dropdown .dropdown-item p {
            white-space: normal;
            word-wrap: break-word; 
            overflow-wrap: break-word;
            text-overflow: ellipsis;
            width: 100%;
        }

        .notification-dropdown .dropdown-item {
            white-space: normal; 
            padding-right: 15px; 
        }

        .icon-circle {
            height: 2.5rem;
            width: 2.5rem;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    @stack('styles')
</head>
<body class="{{ request()->is('admin*') ? 'bg-admin' : 'bg-light' }}">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">LMB Dashboard</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">Tasks</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('projects.index') }}">Projects</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('timesheet.index') }}">Timesheet</a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @auth
                            @if(auth()->user()->can('manage users'))
                                <li class="nav-item dropdown">
                                    <a id="dashboardDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>Admin</a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dashboardDropdown">
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-house me-1"></i> Home
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.projects.index') }}">
                                            <i class="bi bi-card-list me-1"></i> {{ __('Projects') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                            <i class="bi bi-people me-1"></i> {{ __('Users') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.activities.index') }}">
                                            <i class="bi bi-activity me-1"></i> {{ __('Activities') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.vacation-settings.index') }}">
                                            <i class="bi bi-calendar-check me-1"></i> {{ __('Vacations') }}
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('admin.departments.index') }}">
                                            <i class="bi bi-building me-1"></i> {{ __('Departments') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.roles.index') }}">
                                            <i class="bi bi-shield me-1"></i> {{ __('Roles') }}
                                        </a>
                                        @if(isset($project) && $project)
                                        <a class="dropdown-item" href="{{ route('projects.labels.index', $project) }}">
                                            <i class="bi bi-tag me-1"></i> {{ __('Labels') }}
                                        </a>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endauth
                        @auth
                            @if(auth()->user()->pendingApprovals()->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link position-relative" href="{{ route('admin.vacation-settings.index') }}">
                                        <i class="bi bi-bell-fill"></i>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ auth()->user()->pendingApprovals()->count() }}
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endauth
                        @auth
                            <li class="nav-item dropdown">
                                <a id="navbarNotifications" class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-bell"></i>
                                    @if(Auth::user()->unreadNotifications->count() > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ Auth::user()->unreadNotifications->count() > 99 ? '99+' : Auth::user()->unreadNotifications->count() }}
                                        </span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="navbarNotifications">
                                    <h6 class="dropdown-header">Notifications</h6>
                                    
                                    <div id="notification-list">
                                        @forelse(Auth::user()->unreadNotifications()->take(5)->get() as $notification)
                                            <a class="dropdown-item d-flex align-items-center" href="{{ $notification->data['link'] }}">
                                                <div class="me-3">
                                                    <div class="bg-primary icon-circle">
                                                        <i class="bi bi-card-checklist text-white"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="small text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                    <p class="mb-0">{{ $notification->data['message'] }}</p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="dropdown-item text-center">
                                                <p class="mb-0">No new notifications</p>
                                            </div>
                                        @endforelse
                                    </div>
                                    
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">View All Notifications</a>
                                </div>
                            </li>
                        @endauth
                        <!-- Authentication Links -->
                        @guest
                            <!--@if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif-->
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="bi bi-person me-1"></i> My Profile
                                    </a>
                                    <a class="dropdown-item" href="{{ route('vacation.index') }}">
                                        <i class="bi bi-calendar3 me-1"></i> My Calendar
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-1"></i> {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    
    @stack('scripts')

    @auth
        <script src="{{ asset('js/notifications.js') }}"></script>
    @endauth
</body>
</html>