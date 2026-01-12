<!DOCTYPE html>
<html lang="en" x-data="{dark:false}">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">

<div class="flex min-h-screen">

    @include('components.sidebar')

    <div class="flex-1">

        @include('components.navbar')

        <main class="p-6">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>
