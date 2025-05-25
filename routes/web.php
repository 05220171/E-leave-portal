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
            Route::resource('leave-types', LeaveTypeController::class)->except(['show']);

            Route::resource('departments', DepartmentController::class);

            // --- API Routes for Dependent Dropdowns (Protected by 'role:admin') ---
            Route::prefix('api') // Further prefixing to /superadmin/api/
                 ->name('api.')   // Further naming to superadmin.api.
                 ->group(function () {
                    // Expects a department ID or model instance, e.g., /superadmin/api/departments/1/programs
                    Route::get('departments/{department}/programs', [DependentDropdownController::class, 'getPrograms'])
                         ->name('departments.programs');

                    // Expects a program ID or model instance (or code if you adjust controller),
                    // e.g., /superadmin/api/programs/DCSN/classes or /superadmin/api/programs/5/classes
                    Route::get('programs/{program}/classes', [DependentDropdownController::class, 'getClasses'])
                         ->name('programs.classes');
            });

            
            Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
            // --- ADD LEAVE WORKFLOW ROUTES (Nested under leave-types) ---
            Route::prefix('leave-types/{leaveType}/workflows')->name('leave-types.workflows.')
                ->group(function () {
                    Route::get('/', [LeaveWorkflowController::class, 'index'])->name('index');
                    Route::post('/', [LeaveWorkflowController::class, 'store'])->name('store');
                    Route::delete('/{workflow}', [LeaveWorkflowController::class, 'destroy'])->name('destroy');
                    // Optional Edit/Update routes if you implement the edit methods
                    // Route::get('/{workflow}/edit', [LeaveWorkflowController::class, 'edit'])->name('edit');
                    // Route::put('/{workflow}', [LeaveWorkflowController::class, 'update'])->name('update');
            });
            // --- END LEAVE WORKFLOW ROUTES ---
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
        Route::get('/leave-status', [StudentLeaveController::class, 'status'])->name('leave-status');// NOW shows APPROVED leaves + download
        // NEW ROUTE FOR DOWNLOADING CERTIFICATE
        Route::get('/leave-certificate/{leave}/download', [StudentLeaveController::class, 'downloadLeaveCertificate'])->name('leave.download-certificate');
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
        Route::get('/approved-records', [HodController::class, 'approvedRecords'])->name('approved-records');
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
        Route::get('/approved-records', [DsaController::class, 'approvedRecords'])->name('approved-records');
    });



    /*
    |--------------------------------------------------------------------------
    | DAA Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:daa']) // Uses your CheckUserRole middleware
         ->prefix('daa')
         ->name('daa.')
         ->group(function () {
        Route::get('/dashboard', [DaaController::class, 'dashboard'])->name('dashboard');
        // Add other DAA specific routes here later
        // Example: Route::get('/pending-medical-leaves', [DaaController::class, 'pendingMedical'])->name('pending-medical');
    });

    /*
    |--------------------------------------------------------------------------
    | President Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:president']) // Uses your CheckUserRole middleware
         ->prefix('president')
         ->name('president.')
         ->group(function () {
        Route::get('/dashboard', [PresidentController::class, 'dashboard'])->name('dashboard');
        // Add other President specific routes here later
        // Example: Route::get('/approved-medical-leaves', [PresidentController::class, 'approvedMedical'])->name('approved-medical');
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
        Route::post('/leaves/{leave}/mark-recorded', [SSOController::class, 'markAsRecorded'])->name('leaves.mark-recorded');
        });
        // Route::post('/approve/{id}', [SSOController::class, 'approveLeave'])->name('approve-leave');
        // Route::post('/reject/{id}', [SSOController::class, 'rejectLeave'])->name('reject-leave');

});

// require __DIR__.'/auth.php';