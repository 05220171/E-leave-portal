<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentLeaveController;
use App\Http\Controllers\HodController;
use App\Http\Controllers\DsaController;
use App\Http\Controllers\SSOController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\DepartmentController; 
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('landing');

Route::get('/import-users', [ExcelImportController::class, 'importForm'])->name('import.users.form');
Route::post('/import-users', [ExcelImportController::class, 'import'])->name('import.users.store');

/*
|--------------------------------------------------------------------------
| Authenticated Routes - Basic Group (Mainly for Redirect Logic)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'handleRedirect'])->name('home');
});

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Routes (Jetstream Standard + Role Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Formerly "Super Admin" in middleware, now uses 'admin' role)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])
         ->prefix('superadmin')
         ->name('superadmin.')
         ->group(function () {

            Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
            Route::resource('users', SuperAdminController::class)->except(['show']);
            Route::get('users/import/form', [SuperAdminController::class, 'importForm'])->name('users.importForm');
            Route::post('users/import', [SuperAdminController::class, 'import'])->name('users.import');

            // ✅ 2. ADD THIS LINE FOR DEPARTMENT ROUTES
            Route::resource('departments', DepartmentController::class);

        });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    // ... (rest of your student routes remain unchanged) ...
    Route::middleware(['role:student'])
         ->prefix('student')
         ->name('student.')
         ->group(function () {
        Route::get('/apply-leave', [StudentLeaveController::class, 'create'])->name('apply-leave');
        Route::post('/apply-leave', [StudentLeaveController::class, 'store'])->name('store-leave');
        Route::get('/leave-history', [StudentLeaveController::class, 'history'])->name('leave-history');
        Route::delete('/leave/{id}', [StudentLeaveController::class, 'delete'])->name('delete-leave');
        Route::post('/cancel-leave/{id}', [StudentLeaveController::class, 'cancel'])->name('cancel-leave');
        Route::get('/leave-status', [StudentLeaveController::class, 'status'])->name('leave-status');
    });

    /*
    |--------------------------------------------------------------------------
    | HOD Routes
    |--------------------------------------------------------------------------
    */
    // ... (rest of your HOD routes remain unchanged) ...
     Route::middleware(['role:hod'])
         ->prefix('hod')
         ->name('hod.')
         ->group(function () {
        Route::get('/dashboard', [HodController::class, 'index'])->name('dashboard');
        Route::get('/student-history', [HodController::class, 'studentHistory'])->name('student-history');
        Route::post('/approve/{id}', [HodController::class, 'approve'])->name('approve-leave');
        Route::post('/reject/{id}', [HodController::class, 'reject'])->name('reject-leave');
    });


    /*
    |--------------------------------------------------------------------------
    | DSA Routes
    |--------------------------------------------------------------------------
    */
    // ... (rest of your DSA routes remain unchanged) ...
    Route::middleware(['role:dsa'])
         ->prefix('dsa')
         ->name('dsa.')
         ->group(function () {
        Route::get('/dashboard', [DsaController::class, 'index'])->name('dashboard');
        Route::post('/approve/{id}', [DsaController::class, 'approve'])->name('approve');
        Route::post('/reject/{id}', [DsaController::class, 'reject'])->name('reject');
    });


    /*
    |--------------------------------------------------------------------------
    | SSO Routes
    |--------------------------------------------------------------------------
    */
    // ... (rest of your SSO routes remain unchanged) ...
    Route::middleware(['role:sso'])
         ->prefix('sso')
         ->name('sso.')
         ->group(function () {
        Route::get('/dashboard', [SSOController::class, 'dashboard'])->name('dashboard');
        Route::post('/approve/{id}', [SSOController::class, 'approveLeave'])->name('approve-leave');
        Route::post('/reject/{id}', [SSOController::class, 'rejectLeave'])->name('reject-leave');
    });

});

// require __DIR__.'/auth.php';