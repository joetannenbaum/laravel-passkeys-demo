<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return view('authed', [
            'user' => Auth::user(),
        ]);
    }

    return view('home');
});

Route::post('logout', function () {
    Auth::logout();

    return redirect('/');
});

Route::prefix('registration')->controller(RegistrationController::class)->group(function () {
    Route::post('/options', 'generateOptions');
    Route::post('/verify', 'verify');
});

Route::prefix('authentication')->controller(AuthenticationController::class)->group(function () {
    Route::post('/options', 'generateOptions');
    Route::post('/verify', 'verify');
});
