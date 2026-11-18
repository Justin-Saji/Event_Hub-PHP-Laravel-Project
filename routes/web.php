<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminRegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Homepage and Event Details
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Event Registration Page, Submission, and Dedicated Confirmation
Route::get('/events/{event}/register', [RegistrationController::class, 'create'])->name('registrations.create');
Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('registrations.store');
Route::get('/registrations/{registration}/success', [RegistrationController::class, 'success'])->name('registrations.success');

/*
|--------------------------------------------------------------------------
| User Authentication Routes (Signup, Login, Logout)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Signup / Register
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/signup', [AuthController::class, 'showRegisterForm'])->name('signup');
    Route::post('/signup', [AuthController::class, 'register'])->name('signup.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/my-account', [ProfileController::class, 'show'])->name('account');
});

/*
|--------------------------------------------------------------------------
| Dedicated Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

/*
|--------------------------------------------------------------------------
| Protected Admin Routes
|--------------------------------------------------------------------------
| Guarded by 'auth' and custom 'admin' authorization middleware.
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Overview Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Registration Management
    Route::get('/registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}', [AdminRegistrationController::class, 'show'])->name('registrations.show');
    Route::delete('/registrations/{registration}', [AdminRegistrationController::class, 'destroy'])->name('registrations.destroy');
});
