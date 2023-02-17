@extends('layout')

@section('content')
<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" x-data="authForm" x-cloak>
    <div class="p-4 rounded-md bg-yellow-50" x-show="!browserSupported">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M8.485 3.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 3.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    Your browser isn't supported!
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>
                        That's sort of a bummer, sorry. Maybe you have access to a browser that does though,
                        <a target="_blank" class="underline" href="https://caniuse.com/?search=webauthn">
                            check and see
                        </a>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="browserSupported" class="px-4 py-8 bg-white shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="submit">
            <h2 class="text-xl font-bold text-center">
                Sign In or Register
            </h2>
            <div x-show="mode === 'login'">
                <label for="email" class="block text-sm font-medium text-gray-700">Username</label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <input x-model="username" type="text" id="username" v-model="username" autocomplete="off" required
                        autocapitalize="off"
                        class="block w-full px-3 py-2 placeholder-gray-400 border rounded-md appearance-none focus:outline-none sm:text-sm"
                        :class="error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-sky-500 focus:ring-sky-500'" />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="error">
                        <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <p class="mt-2 text-sm text-red-600 " x-show=" error" x-text="error"></p>
            </div>

            <div x-show="mode === 'confirmRegistration'" class="space-y-2 text-center">
                <p>No account exists for "<span x-text="username"></span>".
                <p>Do you want to create a new account?</p>
                <p class="text-sm text-center">
                    <a href="#" @click.prevent="mode = 'login'" class="underline">Cancel</a>
                </p>
            </div>

            <div>
                <button type="submit"
                    class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                    x-text="mode === 'confirmRegistration' ? 'Register' : 'Continue'"
                    x-bind:disabled="username.trim() === ''">
                </button>
            </div>
        </form>
    </div>
</div>
@endsection