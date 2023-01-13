@extends('layout')

@section('content')

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

@endsection