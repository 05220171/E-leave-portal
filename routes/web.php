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
use App\Http\Controllers\DaaController;
use App\Http\Controllers\PresidentController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveWorkflowController;
use App\Http\Controllers\DependentDropdownController; // Create this controller

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('landing');

// Note: This route for import seems general. The superadmin also has import. Clarify if this is intended.
// Route::get('/import-users', [ExcelImportController::class, 'importForm'])->name('import.users.form');
// Route::post('/import-users', [ExcelImportController::class, 'import'])->name('import.users.store');

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
    Route::middleware(['role:admin']) // Ensure this middleware correctly identifies superadmins/admins
         ->prefix('superadmin')
         ->name('superadmin.')
         ->group(function () {

            Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');

            // User Management (Original resource route + specific user type views)
            Route::resource('users', SuperAdminController::class)->except(['show']);
            Route::get('users/import/form', [SuperAdminController::class, 'importForm'])->name('users.importForm');
            Route::post('users/import', [SuperAdminController::class, 'import'])->name('users.import');

            // New routes for managing specific user types
            Route::get('/users/students', [SuperAdminController::class, 'manageStudents'])->name('users.students');
            Route::get('/users/staff', [SuperAdminController::class, 'manageStaffs'])->name('users.staff');
            // End User Management

            Route::resource('departments', DepartmentController::class); // Assuming this is for superadmin
            Route::resource('leave-types', LeaveTypeController::class)->except(['show']);

            // --- API Routes for Dependent Dropdowns (Protected by 'role:admin') ---
            Route::prefix('api') // Further prefixing to /superadmin/api/
                 ->name('api.')   // Further naming to superadmin.api.
                 ->group(function () {
                    Route::get('departments/{department}/programs', [DependentDropdownController::class, 'getPrograms'])
                         ->name('departments.programs');
                    Route::get('programs/{program}/classes', [DependentDropdownController::class, 'getClasses'])
                         ->name('programs.classes');
            });

            // --- ADD LEAVE WORKFLOW ROUTES (Nested under leave-types) ---
            Route::prefix('leave-types/{leaveType}/workflows')->name('leave-types.workflows.')
                ->group(function () {
                    Route::get('/', [LeaveWorkflowController::class, 'index'])->name('index');
                    Route::post('/', [LeaveWorkflowController::class, 'store'])->name('store');
                    Route::delete('/{workflow}', [LeaveWorkflowController::class, 'destroy'])->name('destroy');
            });
            // --- END LEAVE WORKFLOW ROUTES ---
        });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
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
        Route::get('/leave-certificate/{leave}/download', [StudentLeaveController::class, 'downloadLeaveCertificate'])->name('leave.download-certificate');
    });

    /*
    |--------------------------------------------------------------------------
    | HOD Routes
    |--------------------------------------------------------------------------
    */
     Route::middleware(['role:hod'])
         ->prefix('hod')
         ->name('hod.')
         ->group(function () {
        Route::get('/dashboard', [HodController::class, 'index'])->name('dashboard');
        Route::get('/student-history', [HodController::class, 'studentHistory'])->name('student-history');
        Route::post('/approve/{id}', [HodController::class, 'approve'])->name('approve-leave');
        Route::post('/reject/{id}', [HodController::class, 'reject'])->name('reject-leave');
        Route::get('/approved-records', [HodController::class, 'approvedRecords'])->name('approved-records');
    });

    /*
    |--------------------------------------------------------------------------
    | DSA Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:dsa'])
         ->prefix('dsa')
         ->name('dsa.')
         ->group(function () {
        Route::get('/dashboard', [DsaController::class, 'index'])->name('dashboard');
        Route::post('/approve/{id}', [DsaController::class, 'approve'])->name('approve');
        Route::post('/reject/{id}', [DsaController::class, 'reject'])->name('reject');
        Route::get('/approved-records', [DsaController::class, 'approvedRecords'])->name('approved-records');
    });

    /*
    |--------------------------------------------------------------------------
    | DAA Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:daa'])
         ->prefix('daa')
         ->name('daa.')
         ->group(function () {
        Route::get('/dashboard', [DaaController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | President Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:president'])
         ->prefix('president')
         ->name('president.')
         ->group(function () {
        Route::get('/dashboard', [PresidentController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | SSO Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:sso'])
         ->prefix('sso')
         ->name('sso.')
         ->group(function () {
        Route::get('/dashboard', [SSOController::class, 'dashboard'])->name('dashboard');
        Route::post('/leaves/{leave}/mark-recorded', [SSOController::class, 'markAsRecorded'])->name('leaves.mark-recorded');
        });
});

// If you are using Laravel Jetstream or Breeze, they usually have their own auth routes file.
// If you have a custom one, ensure it's correctly included.
// For example: require __DIR__.'/auth.php';