{{-- resources/views/auth/login.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - KaafiyeWiFi</title>

    {{-- Apply theme ASAP (before paint) --}}
    <script>
        (function () {
            try {
                const saved = localStorage.getItem('theme'); // 'dark' | 'light'
                const isDark = saved
                    ? saved === 'dark'
                    : window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

                document.documentElement.classList.toggle('dark', !!isDark);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 overflow-hidden">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">

            {{-- Card --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-lg
                        dark:border-gray-800 dark:bg-gray-900">
                <div class="px-6 py-10 sm:px-10 sm:py-12">

                    {{-- Header --}}
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full
                                    bg-gray-100 dark:bg-gray-800">
                            <img src="{{ asset('assets/logo.svg') }}"
                                 alt="Kaafiye WiFi"
                                 class="h-8 w-8"
                                 loading="lazy">
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                            KAAFIYE WIFI
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Login</p>
                    </div>

                    {{-- Status --}}
                    <x-auth-session-status class="mt-5" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-6">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                Email Address
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="your@email.com"
                                class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm
                                       text-gray-900 placeholder:text-gray-400
                                       focus:border-[#ff4b2b] focus:ring-2 focus:ring-[#ff4b2b]/30
                                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                Password
                            </label>

                            <div class="relative mt-2">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    placeholder="Enter your password"
                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 pr-12 text-sm
                                           text-gray-900 placeholder:text-gray-400
                                           focus:border-[#ff4b2b] focus:ring-2 focus:ring-[#ff4b2b]/30
                                           dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500"
                                />

                                <button type="button"
                                        id="togglePassword"
                                        class="absolute inset-y-0 right-3 flex items-center
                                               text-gray-500 hover:text-[#ff4b2b] dark:text-gray-400">
                                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                 c4.478 0 8.268 2.943 9.542 7
                                                 -1.274 4.057-5.064 7-9.542 7
                                                 -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19
                                                 c-4.478 0-8.268-2.943-9.543-7
                                                 a9.956 9.956 0 012.042-3.368" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M6.223 6.223A9.956 9.956 0 0112 5
                                                 c4.478 0 8.268 2.943 9.543 7
                                                 a9.97 9.97 0 01-4.293 5.067" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        {{-- Remember + Register --}}
                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox"
                                       name="remember"
                                       class="h-4 w-4 rounded border-gray-300 text-[#ff4b2b]
                                              focus:ring-[#ff4b2b] dark:border-gray-700 dark:bg-gray-800">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Remember me</span>
                            </label>

                            <a href="{{ route('register') }}"
                               class="text-sm font-semibold text-[#ff4b2b] hover:opacity-90">
                            </a>
                        </div>

                        {{-- Button --}}
                        <button type="submit"
                                class="w-full rounded-xl py-3.5 text-sm font-semibold text-white
                                       bg-[#ff4b2b] hover:bg-[#e94327] transition">
                            Login
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        const input = document.getElementById('password');
        const btn = document.getElementById('togglePassword');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        btn?.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isPassword);
            eyeClosed.classList.toggle('hidden', !isPassword);
        });
    </script>
</body>
</html>
