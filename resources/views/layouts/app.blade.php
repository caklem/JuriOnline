<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'JuriOnline'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Premium Global Styles -->
    <link rel="stylesheet" href="{{ asset('css/premium-global.css') }}">

    <style>
        .navbar {
            background-color: #1e293b;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #334155;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .navbar-menu {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .navbar-menu a,
        .navbar-menu button {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-family: inherit;
        }

        .navbar-menu a:hover,
        .navbar-menu button:hover {
            color: #60a5fa;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.9375rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background-color: #16a34a;
            color: white;
        }

        .btn-success:hover {
            background-color: #15803d;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-left: 3px solid #16a34a;
            border-radius: 6px;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 3px solid #dc2626;
            border-radius: 6px;
        }

        .alert-warning {
            background-color: #fffbeb;
            color: #854d0e;
            border-left: 3px solid #f59e0b;
            border-radius: 6px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9375rem;
            transition: all 0.2s;
            background: white;
            color: #1e293b;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Sidebar Styles */
        .layout-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
            border-right: 1px solid #334155;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(37, 99, 235, 0.15);
            color: white;
            border-left-color: #2563eb;
        }

        .sidebar-menu a i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        /* Event Management Submenu Styles */
        .event-management-header {
            padding: 0.5rem 1.5rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
            user-select: none;
        }

        .event-management-header:hover {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.8);
        }

        .event-management-header .chevron {
            transition: transform 0.3s;
            font-size: 0.875rem;
        }

        .event-management-header.collapsed .chevron {
            transform: rotate(-90deg);
        }

        .event-management-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
            opacity: 1;
        }

        .event-management-submenu.collapsed {
            max-height: 0;
            opacity: 0;
        }

        .event-management-submenu li {
            margin: 0;
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .top-navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            font-weight: 600;
            color: #2c3e50;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: #2563eb;
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 1001;
        }

        /* Responsive Styles */
        @media (min-width: 1400px) {
            .container {
                max-width: 1600px;
                padding: 0 2.5rem;
            }
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 260px;
                width: calc(100% - 260px);
            }

            .container {
                padding: 0 1.5rem;
                max-width: 100%;
            }

            .card {
                padding: 1.25rem;
            }

            .top-navbar {
                padding: 1rem 1.5rem;
            }

            table {
                font-size: 0.875rem;
            }

            table th,
            table td {
                padding: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 260px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .top-navbar {
                padding: 0.75rem 1rem;
            }

            .user-info {
                font-size: 0.875rem;
            }

            .container {
                margin: 1rem auto;
                padding: 0 1rem;
                max-width: 100%;
            }

            .navbar-menu {
                flex-direction: column;
                gap: 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            /* Responsive tables */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 0.875rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            /* Cards responsive */
            .card {
                margin-bottom: 1rem;
                padding: 1rem;
            }

            /* Alert responsive */
            .alert {
                padding: 0.75rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .sidebar-header {
                font-size: 1rem;
                padding: 1rem;
            }

            .top-navbar {
                padding: 0.5rem 0.75rem;
            }

            .user-menu {
                gap: 0.5rem;
            }

            .user-info {
                display: none;
            }

            .container {
                padding: 0 0.75rem;
            }

            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .card {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            h3 {
                font-size: 1.1rem;
            }

            .alert {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            table {
                font-size: 0.75rem;
            }

            table th,
            table td {
                padding: 0.5rem;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased" style="background: #f5f7fa;">
    @auth
    <div class="layout-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('home') }}" style="color: white; text-decoration: none;">JuriOnline</a>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i>🏠</i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if(auth()->user()->role && (auth()->user()->isOrganizer() || auth()->user()->isAdmin() || auth()->user()->isAdminOwner()))
                <li>
                    <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.index') ? 'active' : '' }}">
                        <i>📅</i>
                        <span>Event Saya</span>
                    </a>
                </li>
                @endif

                @php
                $currentEvent = null;
                if (request()->route('event')) {
                    $currentEvent = request()->route('event');
                    // Load relationships if not already loaded
                    if (!$currentEvent->relationLoaded('eventForm')) {
                        $currentEvent->load('eventForm');
                    }
                    if (!$currentEvent->relationLoaded('juryFormTemplate')) {
                        $currentEvent->load('juryFormTemplate');
                    }
                    if (!$currentEvent->relationLoaded('registrations')) {
                        $currentEvent->load('registrations');
                    }
                } elseif (request()->routeIs('events.show') || request()->routeIs('events.edit') || request()->routeIs('events.form.*') || request()->routeIs('events.jury-form.*') || request()->routeIs('events.participants') || request()->routeIs('jury.leaderboard') || request()->routeIs('events.recap') || request()->routeIs('events.jury-assignments.*')) {
                    // Try to get event from route parameter
                    try {
                        $eventId = request()->route()->parameter('event');
                        if ($eventId) {
                            $currentEvent = \App\Models\Event::with(['eventForm', 'juryFormTemplate', 'registrations'])->find($eventId);
                        }
                    } catch (\Exception $e) {
                        // Ignore if event not found
                    }
                }
                @endphp

                @if($currentEvent && (auth()->user()->isOrganizer() && $currentEvent->organizer_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isAdminOwner()))
                <li style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="event-management-header" id="eventManagementHeader" onclick="toggleEventManagement()">
                        <span>Kelola Event</span>
                        <span class="chevron">▼</span>
                    </div>
                </li>
                <li>
                    <ul class="event-management-submenu" id="eventManagementSubmenu">
                        <li>
                            <a href="{{ route('events.show', $currentEvent) }}" class="{{ request()->routeIs('events.show') ? 'active' : '' }}">
                                <i>📄</i>
                                <span>Detail Event</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('events.edit', $currentEvent) }}" class="{{ request()->routeIs('events.edit') ? 'active' : '' }}">
                                <i>✏️</i>
                                <span>Edit Event</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('events.form.create', $currentEvent) }}" class="{{ request()->routeIs('events.form.*') ? 'active' : '' }}">
                                <i>📋</i>
                                <span>Form Pendaftaran</span>
                                @if($currentEvent->eventForm)
                                <span style="margin-left: auto; background: #27ae60; color: white; padding: 0.15rem 0.4rem; border-radius: 10px; font-size: 0.7rem; font-weight: bold;">✓</span>
                                @endif
                            </a>
                        </li>
                        @if(auth()->user()->isAdmin() || auth()->user()->isAdminOwner())
                        <li>
                            <a href="{{ route('events.jury-form.create', $currentEvent) }}" class="{{ request()->routeIs('events.jury-form.*') ? 'active' : '' }}">
                                <i>⚖️</i>
                                <span>Form Penilaian</span>
                                @if($currentEvent->juryFormTemplate)
                                <span style="margin-left: auto; background: #27ae60; color: white; padding: 0.15rem 0.4rem; border-radius: 10px; font-size: 0.7rem; font-weight: bold;">✓</span>
                                @endif
                            </a>
                        </li>
                        @if($currentEvent->juryFormTemplate)
                        <li>
                            <a href="#" onclick="if(typeof showJuryFormPreview === 'function') { showJuryFormPreview(); return false; } else { alert('Fungsi preview belum tersedia di halaman ini'); return false; }" style="cursor: pointer;">
                                <i>👁️</i>
                                <span>Preview Form Juri</span>
                            </a>
                        </li>
                        @endif
                        @endif
                        <li>
                            <a href="{{ route('events.participants', $currentEvent) }}" class="{{ request()->routeIs('events.participants') ? 'active' : '' }}">
                                <i>👥</i>
                                <span>Daftar Peserta</span>
                                <span style="margin-left: auto; background: #3498db; color: white; padding: 0.15rem 0.4rem; border-radius: 10px; font-size: 0.7rem; font-weight: bold;">{{ $currentEvent->registrations->count() }}</span>
                            </a>
                        </li>
                        @if($currentEvent->registrations->isNotEmpty())
                        <li>
                            <a href="{{ route('jury.leaderboard', $currentEvent) }}" class="{{ request()->routeIs('jury.leaderboard') ? 'active' : '' }}">
                                <i>🏆</i>
                                <span>Leaderboard</span>
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('payments.pending') }}?event={{ $currentEvent->id }}" class="{{ request()->routeIs('payments.*') && request()->get('event') == $currentEvent->id ? 'active' : '' }}">
                                <i>💳</i>
                                <span>Verifikasi Pembayaran</span>
                            </a>
                        </li>
                <li>
                    <a href="{{ route('events.recap', $currentEvent) }}" class="{{ request()->routeIs('events.recap') ? 'active' : '' }}">
                        <i>📊</i>
                        <span>Rekapitulasi Nilai</span>
                    </a>
                </li>
                @if($currentEvent->juryFormTemplates()->count() > 0)
                <li>
                    <a href="{{ route('events.jury-assignments.index', $currentEvent) }}" class="{{ request()->routeIs('events.jury-assignments.*') ? 'active' : '' }}">
                        <i>👥</i>
                        <span>Assign Form ke Juri</span>
                        @php
                            $assignedCount = \App\Models\EventJuryFormAssignment::where('event_id', $currentEvent->id)->count();
                        @endphp
                        @if($assignedCount > 0)
                        <span style="margin-left: auto; background: #3498db; color: white; padding: 0.15rem 0.4rem; border-radius: 10px; font-size: 0.7rem; font-weight: bold;">{{ $assignedCount }}</span>
                        @endif
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

                @if(auth()->user()->role && auth()->user()->isParticipant())
                <li>
                    <a href="{{ route('events.public') }}" class="{{ request()->routeIs('events.public') ? 'active' : '' }}">
                        <i>🎯</i>
                        <span>Semua Event</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('registrations.my') }}" class="{{ request()->routeIs('registrations.*') ? 'active' : '' }}">
                        <i>📋</i>
                        <span>Pendaftaran Saya</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->role && (auth()->user()->isJury() || auth()->user()->isAdmin() || auth()->user()->isAdminOwner()))
                <li>
                    <a href="{{ route('jury.events') }}" class="{{ request()->routeIs('jury.events') || request()->routeIs('jury.score') ? 'active' : '' }}">
                        <i>⚖️</i>
                        <span>Penilaian</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->role && auth()->user()->canVerifyPayments())
                <li>
                    <a href="{{ route('payments.pending') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <i>💳</i>
                        <span>Verifikasi Pembayaran</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->role && (auth()->user()->isAdmin() || auth()->user()->isAdminOwner()))
                <li>
                    <a href="{{ route('jury.all-scores') }}" class="{{ request()->routeIs('jury.all-scores') ? 'active' : '' }}">
                        <i>📊</i>
                        <span>Semua Penilaian</span>
                    </a>
                </li>
                @endif

                <!-- About Section -->
                <li style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">
                        <i>ℹ️</i>
                        <span>Tentang JuriOnline</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('company-profile') }}" class="{{ request()->routeIs('company-profile') ? 'active' : '' }}">
                        <i>🏢</i>
                        <span>Company Profile</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
            ☰
        </button>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="top-navbar">
                <div class="user-menu">
                    <span class="user-info">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem;">
                            Logout
                        </button>
                    </form>
                </div>
            </nav>

            <!-- Content Container -->
            <div class="container">
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    @else
    <!-- Guest Layout -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('home') }}" class="navbar-brand">JuriOnline</a>
            <ul class="navbar-menu">
                <li><a href="{{ route('events.public') }}">Event Tersedia</a></li>
                <li><a href="{{ route('about') }}">Tentang</a></li>
                <li><a href="{{ route('company-profile') }}">Company Profile</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        @yield('content')
    </div>
    @endauth

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768 && sidebar && toggle) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>

    @stack('scripts')
    
    <script>
        // Event Management Menu Toggle
        function toggleEventManagement() {
            const header = document.getElementById('eventManagementHeader');
            const submenu = document.getElementById('eventManagementSubmenu');
            
            if (!header || !submenu) return;
            
            const isCollapsed = header.classList.contains('collapsed');
            
            if (isCollapsed) {
                // Expand
                header.classList.remove('collapsed');
                submenu.classList.remove('collapsed');
                localStorage.setItem('eventManagementMenuCollapsed', 'false');
            } else {
                // Collapse
                header.classList.add('collapsed');
                submenu.classList.add('collapsed');
                localStorage.setItem('eventManagementMenuCollapsed', 'true');
            }
        }
        
        // Initialize menu state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('eventManagementHeader');
            const submenu = document.getElementById('eventManagementSubmenu');
            
            if (!header || !submenu) return;
            
            // Check if current page is one of the event management pages
            const isEventManagementPage = {{ 
                request()->routeIs('events.show') || 
                request()->routeIs('events.edit') || 
                request()->routeIs('events.form.*') || 
                request()->routeIs('events.jury-form.*') || 
                request()->routeIs('events.participants') || 
                request()->routeIs('jury.leaderboard') 
                ? 'true' : 'false' 
            }};
            
            // Get saved state from localStorage
            const savedState = localStorage.getItem('eventManagementMenuCollapsed');
            
            // If on event management page, always open. Otherwise, use saved state (default: open)
            if (isEventManagementPage) {
                header.classList.remove('collapsed');
                submenu.classList.remove('collapsed');
            } else if (savedState === 'true') {
                header.classList.add('collapsed');
                submenu.classList.add('collapsed');
            } else {
                // Default: open
                header.classList.remove('collapsed');
                submenu.classList.remove('collapsed');
            }
        });
    </script>
</body>

</html>