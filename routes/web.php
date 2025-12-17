<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::group(['domain'=>config('tenancy.central_domains.0')],function(){

    Route::get('/', function () {
        return view('welcome');
    });
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');


    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});

// SuperAdmin Routes (accessible from any domain, including localhost)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    // Redirect root superadmin path to login
    Route::get('/', function () {
        return redirect()->route('superadmin.login');
    });

    // Guest routes (login)
    Route::middleware('guest:superadmin')->group(function () {
        Route::get('/login', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('/login', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'login']);
        
        // Password reset routes
        Route::get('/password/forgot', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'showForgotPasswordForm'])
            ->name('password.request');
        Route::post('/password/email', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'sendPasswordResetLink'])
            ->name('password.email');
        Route::get('/password/reset/{token}', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'showResetForm'])
            ->name('password.reset');
        Route::post('/password/reset', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'resetPassword'])
            ->name('password.reset.update');
    });

    // Authenticated routes (dashboard)
    Route::middleware('auth:superadmin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\SuperAdminDashboardController::class, 'index'])
            ->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\SuperAdmin\SuperAdminAuthController::class, 'logout'])
            ->name('logout');
        
        // Profile routes
        Route::get('/profile', [App\Http\Controllers\SuperAdmin\SuperAdminProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\SuperAdmin\SuperAdminProfileController::class, 'update'])
            ->name('profile.update');
        Route::put('/password', [App\Http\Controllers\SuperAdmin\SuperAdminProfileController::class, 'updatePassword'])
            ->name('password.update');
        
        // Vendors routes
        Route::get('/vendors', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'index'])
            ->name('vendors.index');
        Route::get('/vendors/export', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'export'])
            ->name('vendors.export');
        Route::get('/vendors/create', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'create'])
            ->name('vendors.create');
        Route::post('/vendors', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'store'])
            ->name('vendors.store');
        Route::get('/vendors/{id}', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'show'])
            ->name('vendors.show');
        Route::get('/vendors/{id}/edit', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'edit'])
            ->name('vendors.edit');
        Route::put('/vendors/{id}', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'update'])
            ->name('vendors.update');
        Route::post('/vendors/{id}/reset-password', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'resetPassword'])
            ->name('vendors.reset-password');
        Route::post('/vendors/{id}/change-status', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'updateStatus'])
            ->name('vendors.update-status');
        Route::get('/vendors/{id}/auto-login-link', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'generateAutoLoginLink'])
            ->name('vendors.auto-login-link');
        Route::post('/vendors/{id}/restore', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'restore'])
            ->name('vendors.restore');
        Route::delete('/vendors/{id}', [App\Http\Controllers\SuperAdmin\VendorsController::class, 'destroy'])
            ->name('vendors.destroy');
    });
});

