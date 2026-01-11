<!DOCTYPE html>
<html lang="en" class="{{ session('dark_mode', false) ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Tailwind CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            @include('partials.navbar')

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @yield('content')
            </main>

            <!-- Footer (optional) -->
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="container mx-auto text-center text-gray-500 dark:text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>
    <script>
        // Initialize dark mode
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();

            // Handle sidebar toggle on mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }

            // Handle dropdown menus
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.nextElementSibling;
                    dropdown.classList.toggle('hidden');
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            });
        });

        // Logout function
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                // In a real Laravel app, this would be a POST request to /logout
                window.location.href = '/logout';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
