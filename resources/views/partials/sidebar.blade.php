<!-- Sidebar -->
<aside id="sidebar" class="sidebar w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col flex-shrink-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-20 fixed md:relative h-full">
    <!-- Logo for sidebar -->
    <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-800">
        <h2 class="text-xl font-bold text-primary-600 dark:text-primary-400">Dashboard</h2>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto p-4">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                    <i class="fas fa-home mr-3 text-lg"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Users -->
            <li class="menu-item">
                <a href="#" class="menu-toggle flex items-center justify-between px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <div class="flex items-center">
                        <i class="fas fa-users mr-3 text-lg"></i>
                        <span>Users</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </a>
                <ul class="submenu hidden pl-4 mt-1 space-y-1">
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-list mr-2"></i>
                            <span>All Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-user-plus mr-2"></i>
                            <span>Add New</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-user-check mr-2"></i>
                            <span>Roles & Permissions</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Products -->
            <li class="menu-item">
                <a href="#" class="menu-toggle flex items-center justify-between px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <div class="flex items-center">
                        <i class="fas fa-box mr-3 text-lg"></i>
                        <span>Products</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </a>
                <ul class="submenu hidden pl-4 mt-1 space-y-1">
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-list mr-2"></i>
                            <span>Product List</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span>Add Product</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-tags mr-2"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Orders -->
            <li>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <i class="fas fa-shopping-cart mr-3 text-lg"></i>
                    <span>Orders</span>
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">5</span>
                </a>
            </li>

            <!-- Analytics -->
            <li>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <i class="fas fa-chart-bar mr-3 text-lg"></i>
                    <span>Analytics</span>
                </a>
            </li>

            <!-- Messages -->
            <li>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <i class="fas fa-envelope mr-3 text-lg"></i>
                    <span>Messages</span>
                    <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full">12</span>
                </a>
            </li>

            <!-- Settings -->
            <li class="menu-item">
                <a href="#" class="menu-toggle flex items-center justify-between px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <div class="flex items-center">
                        <i class="fas fa-cog mr-3 text-lg"></i>
                        <span>Settings</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform"></i>
                </a>
                <ul class="submenu hidden pl-4 mt-1 space-y-1">
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-globe mr-2"></i>
                            <span>General</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-bell mr-2"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-shield-alt mr-2"></i>
                            <span>Security</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>

        <!-- Additional Information -->
        <div class="mt-8 p-4 bg-primary-50 dark:bg-gray-800 rounded-lg">
            <h3 class="font-medium text-primary-700 dark:text-primary-300">Quick Stats</h3>
            <div class="mt-2 space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active Users</span>
                    <span class="text-sm font-medium">1,234</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Orders Today</span>
                    <span class="text-sm font-medium">56</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Revenue</span>
                    <span class="text-sm font-medium">$12,450</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fas fa-headset text-primary-600 dark:text-primary-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">Need Help?</p>
                <a href="#" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">Contact Support</a>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay for mobile sidebar -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden hidden"></div>

<script>
    // Sidebar menu toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.menu-toggle');

        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                const icon = this.querySelector('.fa-chevron-down');

                submenu.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });

        // Close sidebar when clicking overlay
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (sidebarToggle && sidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
    });
</script>
