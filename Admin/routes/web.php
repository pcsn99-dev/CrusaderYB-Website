<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\GenericWriteupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Admin Permission Reference
|--------------------------------------------------------------------------
|
| Seeded permissions for the current admin development cycle:
|
| view-admin-dashboard
| - Allows access to the admin dashboard.
|
| manage-roles
| - Allows access to role management.
| - Used for: roles.index, roles.create, roles.store, roles.show,
|   roles.edit, roles.update, roles.destroy.
|
| manage-admin-users
| - Allows access to admin user management.
| - Used for: admin-users.index, admin-users.create, admin-users.store,
|   admin-users.show, admin-users.edit, admin-users.update,
|   admin-users.destroy, admin-users.activate, admin-users.deactivate,
|   admin-users.resend-temporary-password.
|
| view-writeups
| - Allows viewing submitted writeups.
|
| proofread-writeups
| - Allows proofreading submitted writeups.
|

|
| Add new permissions in RolesAndPermissionsSeeder first, then use them
| in routes through: ->middleware('permission:permission-slug')
|
*/





Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->middleware('guest')
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->middleware('guest')
    ->name('google.callback');




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


    Route::resource('roles', RoleController::class)->middleware('permission:manage-roles');


    Route::resource('admin-users', AdminUserController::class)->middleware('permission:manage-admin-users');

    Route::patch('/admin-users/{adminUser}/activate', [AdminUserController::class, 'activate'])
        ->middleware('permission:manage-admin-users')
        ->name('admin-users.activate');

    Route::patch('/admin-users/{adminUser}/deactivate', [AdminUserController::class, 'deactivate'])
        ->middleware('permission:manage-admin-users')
        ->name('admin-users.deactivate');

    Route::post('/admin-users/{adminUser}/resend-temporary-password', [AdminUserController::class, 'resendTemporaryPassword'])
        ->middleware('permission:manage-admin-users')
        ->name('admin-users.resend-temporary-password');




    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //livewire writeups
    Route::get('/writeups/review', function () {
        return view('writeups.review.index');
    })->middleware('permission:view-writeups')->name('writeups.review.index');

    Route::get('/writeups/review/{writeup}', function (\App\Models\Writeup $writeup) {
        return view('writeups.review.show', compact('writeup'));
    })->middleware('permission:view-writeups')->name('writeups.review.show');

    //generic writeups
    Route::get('/writeups/generic', [GenericWriteupController::class, 'index'])
        ->name('writeups.generic.index');

    Route::post('/writeups/generic', [GenericWriteupController::class, 'store'])
        ->name('writeups.generic.store');

    Route::put('/writeups/generic/{genericWriteup}', [GenericWriteupController::class, 'update'])
        ->name('writeups.generic.update');

    Route::delete('/writeups/generic/{genericWriteup}', [GenericWriteupController::class, 'destroy'])
        ->name('writeups.generic.destroy');


});




require __DIR__.'/auth.php';
