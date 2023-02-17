<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Passkey Authentication Demo | Joe Codes</title>

    <link href="https://fonts.bunny.net/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (config('services.fathom_analytics.id'))
    <script src="https://cdn.usefathom.com/script.js" data-site="{{ config('services.fathom_analytics.id') }}" defer>
    </script>
    @endif
</head>

<body class="h-full">
    <div class="flex flex-col justify-center min-h-full px-4 py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-auto h-12 mx-auto">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 01-3.6 9.75m6.633-4.596a18.666 18.666 0 01-2.485 5.33" />
            </svg>
            <h2 class="mt-6 text-3xl font-bold tracking-tight text-center text-gray-900">Laravel Passkeys Demo</h2>
            <p class="mt-2 text-sm text-center text-gray-600">
                Something here, probably a link to the post and a note that the database deletes info every 30 minutes
            </p>
        </div>

        @yield('content')
    </div>
</body>

</html>