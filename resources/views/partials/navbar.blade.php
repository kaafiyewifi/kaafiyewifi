<nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left side: Mobile menu button and logo -->
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button id="sidebarToggle" class="md:hidden p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Logo -->
                <div class="ml-4 md:ml-0 flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ config('app.name', 'Laravel') }}</h1>
                    </div>
                </div>
            </div>

            <!-- Right side: User menu and dark mode toggle -->
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <div class="flex items-center">
                    <span class="mr-2 text-sm hidden md:block dark:text-gray-300">Light</span>
                    <label for="darkModeToggle" class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="darkModeToggle" class="sr-only peer" onclick="toggleDarkMode()">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                    <span class="ml-2 text-sm hidden md:block dark:text-gray-300">Dark</span>
                </div>

                <!-- User Badge and Dropdown -->
                <div class="relative dropdown">
                    <button class="dropdown-toggle flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                        <!-- User Avatar -->
                        <div class="relative">
                            <div class="h-9 w-9 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-lg">
                                {{ substr(auth()->user()->name ?? 'User', 0, 1) }}
                            </div>
                            <!-- Online status indicator -->
                            <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-gray-800 bg-green-500"></span>
                        </div>

                        <!-- User info (hidden on mobile) -->
                        <div class="hidden md:block text-left">
                            <div class="font-medium">{{ auth()->user()->name ?? 'John Doe' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email ?? 'admin@example.com' }}</div>
                        </div>

                        <!-- Dropdown arrow -->
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-10 border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-sm font-medium">{{ auth()->user()->name ?? 'John Doe' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email ?? 'admin@example.com' }}</p>
                        </div>

                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-bell mr-2"></i> Notifications
                            <span class="ml-2 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">3</span>
                        </a>

                        <div class="border-t border-gray-100 dark:border-gray-700"></div>

                        <button onclick="handleLogout()" class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
