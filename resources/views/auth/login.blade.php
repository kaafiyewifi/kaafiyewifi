<!DOCTYPE html>
<html lang="en"
      x-data="{ show: false }"
      class="h-full">

<head>
    <meta charset="UTF-8">
    <title>Login – Kaafiye Wifi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen flex items-center justify-center
             bg-slate-100 dark:bg-darkBg text-slate-800 dark:text-slate-100">

<div class="w-full max-w-md bg-white dark:bg-darkCard
            border border-slate-200 dark:border-darkBorder
            rounded-2xl shadow-lg p-8">

    <!-- LOGO / TITLE -->
    <div class="text-center mb-8">
        <div class="w-14 h-14 mx-auto mb-3 rounded-full
                    bg-primary text-white flex items-center justify-center
                    text-xl font-bold">
            K
        </div>

        <h1 class="text-2xl font-bold text-primary">
            Kaafiye Wifi
        </h1>
        <p class="text-sm text-slate-500">
            Admin Login
        </p>
    </div>

    <!-- LOGIN FORM -->
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- EMAIL -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Email
            </label>
            <input type="email" name="email" required autofocus
                   class="w-full rounded-lg border border-slate-300
                          dark:border-darkBorder dark:bg-darkBg
                          focus:ring-2 focus:ring-primary focus:border-primary
                          px-4 py-2">
        </div>

        <!-- PASSWORD -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Password
            </label>

            <div class="relative">
                <input :type="show ? 'text' : 'password'"
                       name="password" required
                       class="w-full rounded-lg border border-slate-300
                              dark:border-darkBorder dark:bg-darkBg
                              focus:ring-2 focus:ring-primary focus:border-primary
                              px-4 py-2 pr-12">

                <!-- SHOW / HIDE -->
                <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center
                               text-sm text-primary">
                    <span x-show="!show">Show</span>
                    <span x-show="show">Hide</span>
                </button>
            </div>
        </div>

        <!-- REMEMBER -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember"
                   class="rounded border-slate-300 text-primary focus:ring-primary">
            <span class="text-sm">Remember me</span>
        </div>

        <!-- SUBMIT -->
        <button type="submit"
                class="w-full py-2.5 rounded-lg
                       bg-primary hover:bg-primaryHover
                       text-white font-semibold transition">
            Login
        </button>
    </form>

</div>

</body>
</html>
