<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TMS</title>

    <!-- logo-->

    <link id="favicon" rel="icon" href="imgs/logo2.png" type="image/x-icon" />


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- dashboared -->

    <!-- Scripts -->
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->




    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">

    <!-- Custom Styles -->
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->


    @livewireStyles
    @stack('styles') <!-- hover on cards -->

</head>

<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <!-- <i class="bi bi-layers"></i> -->
                <img src="{{ asset('imgs/logo2.png') }}" alt="logo" class="logo">

            </a>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">

                @can('dashboard.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endcan

                @can('internal-tenders.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('internal-tender') ? 'active' : '' }}" href="{{ route('internal-tender') }}">
                        <i class="bi bi-building"></i>
                        <span>Internal Tender</span>
                    </a>
                </li>
                @endcan



                @can('e-tenders.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('e-tender') ? 'active' : '' }}" href="{{ route('e-tender') }}">
                        <i class="bi bi-display"></i>
                        <span>E-Tender</span>
                    </a>
                </li>
                @endcan


                @can('other-tenders.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('other-tender-platform') ? 'active' : '' }}" href="{{ route('other-tender-platform') }}">
                        <i class="bi bi-collection"></i>
                        <span>Other Platforms</span>
                    </a>
                </li>
                @endcan

                @can('users.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users') ? 'active' : '' }}" href="{{ route('users') }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
                @endcan

                @can('contact-list.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact-list') ? 'active' : '' }}" href="{{ route('contact-list') }}">
                        <i class="bi-card-list"></i>
                        <span>Contact List</span>
                    </a>
                </li>
                @endcan

                @can('roles.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('roles') ? 'active' : '' }}" href="{{ route('roles') }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Roles</span>
                    </a>
                </li>
                @endcan


            </ul>


        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation Bar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0">{{ $header ?? 'Dashboard' }}</h5>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <button class="user-dropdown dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="p-4">
            <main>
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Script (app.js)  -->
    <!-- <script src="{{ asset('/js/app.js') }}"></script> -->

    <!-- script of dashboared -->
    @stack('scripts')



    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/utc.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/timezone.js"></script>

    <!-- الخطوة 2: تفعيل الإضافات (داخل وسم script ) -->
    <script>
        dayjs.extend(window.dayjs_plugin_utc);
        dayjs.extend(window.dayjs_plugin_timezone);
    </script>

    <!-- الخطوة 3: تحميل ملف app.js الخاص بك (مرة واحدة فقط وفي المكان الصحيح) -->
    <script src="{{ asset('/js/app.js') }}"></script>
</body>

</html>