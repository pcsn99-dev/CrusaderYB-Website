<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [PasswordChangeController::class, 'edit'])
        ->name('password.force.edit');

    Route::patch('/force-password-change', [PasswordChangeController::class, 'update'])
        ->name('password.force.update');
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'force.password.change', 'admin.account'])->name('dashboard');



Route::middleware(['auth', 'force.password.change', 'admin.account'])->group(function () {


    Route::resource('roles', RoleController::class);

    
    Route::resource('admin-users', AdminUserController::class);
    Route::patch('/admin-users/{adminUser}/activate', [AdminUserController::class, 'activate'])
        ->name('admin-users.activate');
    Route::patch('/admin-users/{adminUser}/deactivate', [AdminUserController::class, 'deactivate'])
        ->name('admin-users.deactivate');
    Route::post('/admin-users/{adminUser}/resend-temporary-password', [AdminUserController::class, 'resendTemporaryPassword'])
        ->name('admin-users.resend-temporary-password');




    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




require __DIR__.'/auth.php';
