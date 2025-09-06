<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Jadwal Rilis Dataset') }} - @yield('title', 'Satu Data Garut')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ url('/') }}" class="flex items-center">
                                <svg class="w-8 h-8 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="text-xl font-semibold">Satu Data Garut</span>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('public.jadwal') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium 
                                      {{ request()->routeIs('public.*') ? 'text-gray-900 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent' }}">
                                Jadwal Rilis
                            </a>
                            
                            @auth
                                <a href="{{ route('admin.jadwal.index') }}" 
                                   class="inline-flex items-center px-1 pt-1 text-sm font-medium 
                                          {{ request()->routeIs('admin.*') ? 'text-gray-900 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent' }}">
                                    Dashboard Admin
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Right Side Navigation -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        @auth
                            <!-- User Dropdown -->
                            <div class="ml-3 relative">
                                <div class="relative inline-block text-left">
                                    <div class="dropdown">
                                        <button class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                            {{ Auth::user()->name }}
                                            <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <div class="dropdown-content hidden absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Profile
                                            </a>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Logout
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900 mr-4">Login</a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="mobile-menu hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('public.jadwal') }}" 
                       class="block pl-3 pr-4 py-2 text-base font-medium 
                              {{ request()->routeIs('public.*') ? 'text-blue-700 bg-blue-50 border-l-4 border-blue-400' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 border-l-4 border-transparent' }}">
                        Jadwal Rilis
                    </a>
                    
                    @auth
                        <a href="{{ route('admin.jadwal.index') }}" 
                           class="block pl-3 pr-4 py-2 text-base font-medium 
                                  {{ request()->routeIs('admin.*') ? 'text-blue-700 bg-blue-50 border-l-4 border-blue-400' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 border-l-4 border-transparent' }}">
                            Dashboard Admin
                        </a>
                    @endauth
                </div>

                @auth
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <a href="{{ route('login') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            Login
                        </a>
                    </div>
                @endauth
            </div>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-500">
                    © {{ date('Y') }} Satu Data Garut. Pemantauan Jadwal Rilis Dataset.
                </div>
            </div>
        </footer>
    </div>

    <!-- Custom Scripts -->
    <script>
        // Toggle dropdown
        document.querySelectorAll('.dropdown').forEach(function(dropdown) {
            const button = dropdown.querySelector('button');
            const content = dropdown.querySelector('.dropdown-content');
            
            button.addEventListener('click', function() {
                content.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target)) {
                    content.classList.add('hidden');
                }
            });
        });

        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Auto hide flash messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>
