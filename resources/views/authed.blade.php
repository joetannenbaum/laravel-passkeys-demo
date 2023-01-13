<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Passkey Authentication Demo | Joe Codes</title>

    <link href="https://fonts.bunny.net/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-full">

    <div class="flex flex-col justify-center min-h-full px-4 py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-auto h-12 mx-auto" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h2 class="mt-6 text-3xl font-bold tracking-tight text-center text-gray-900">Laravel Passkeys Demo</h2>
            <p class="mt-2 text-sm text-center text-gray-600">
                Something here, probably a link to the post and a note that the database deletes info every 30 minutes
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="px-4 py-8 space-y-4 text-center bg-white shadow sm:rounded-lg sm:px-10">
                <p class="text-xl">👋 Well hey there, <strong>{{ $user->username }}</strong>!</p>
                <div>
                    <form action="/logout" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white border border-transparent rounded-md shadow-sm bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>