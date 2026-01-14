<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('News Feed') }}
                    </x-nav-link>
                    <x-nav-link :href="route('friends.index')" :active="request()->routeIs('friends.index')">
                        {{ __('Friends') }}
                    </x-nav-link>
                    <x-nav-link :href="route('friends.search')" :active="request()->routeIs('friends.search')">
                        {{ __('Search') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Notifications (Alpine + Echo) -->
            <div x-data="{
                notifications: [],
                unreadCount: 0,
                init() {
                    this.fetchNotifications();
                    this.fetchUnreadCount();
                    
                    // Listen for notifications
                    if (window.Echo) {
                         window.Echo.private('App.Models.User.{{ Auth::id() }}')
                            .notification((notification) => {
                                console.log('Notification received:', notification);
                                this.unreadCount++;
                                
                                // Normalize: Real-time data can be flat or wrapped in .data
                                let payload = notification.data ? notification.data : notification;
                                let id = notification.id || payload.id || Math.random();
                                
                                this.notifications.unshift({
                                    id: id,
                                    data: payload,
                                    created_at: notification.created_at || new Date().toISOString(),
                                    read_at: null
                                });
                            });
                    }
                },
                fetchNotifications() {
                    axios.get('{{ route('notifications.index') }}').then(res => this.notifications = res.data);
                },
                fetchUnreadCount() {
                    axios.get('{{ route('notifications.unread') }}').then(res => this.unreadCount = res.data.count);
                },
                markAsRead(id) {
                    axios.post('/notifications/' + id + '/read').then(() => {
                        this.fetchNotifications();
                        this.fetchUnreadCount();
                    });
                },
                markAllRead() {
                    axios.post('{{ route('notifications.readAll') }}').then(() => {
                        this.notifications = [];
                        this.unreadCount = 0;
                    });
                }
            }" class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="w-80" contentClasses="py-0 bg-white dark:bg-gray-800">
                    <x-slot name="trigger">
                        <button
                            class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">View notifications</span>
                            <!-- Bell Icon -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Badge -->
                            <span x-show="unreadCount > 0" x-text="unreadCount"
                                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full"></span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="py-2">
                            <div
                                class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300 flex justify-between items-center">
                                <span>Notifications</span>
                                <button @click.stop="markAllRead" class="text-xs text-blue-600 hover:text-blue-800">Mark
                                    all read</button>
                            </div>

                            <ul class="max-h-64 overflow-y-auto">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <li class="border-b border-gray-100 dark:border-gray-700 last:border-0"
                                        @click.stop="markAsRead(notification.id)">
                                        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 ease-in-out cursor-pointer"
                                            :class="{ 'bg-blue-50 dark:bg-gray-900': !notification.read_at }">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white"
                                                x-text="notification.data.user_name || notification.data.sender_name">
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"
                                                x-text="notification.data.message"></p>
                                            <p class="text-xs text-gray-400 mt-1"
                                                x-text="new Date(notification.created_at).toLocaleTimeString()"></p>
                                        </div>
                                    </li>
                                </template>
                                <div x-show="notifications.length === 0"
                                    class="px-4 py-6 text-center text-gray-500 text-sm">
                                    No notifications
                                </div>
                            </ul>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>