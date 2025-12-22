<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditSessionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\AssetStatusController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //Assets Routes
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('delete');
        Route::post('/bulk-delete', [AssetController::class, 'bulkDestroy'])->name('bulkDelete');
        Route::get('/export', [AssetController::class, 'export'])->name('export');
        Route::post('/import', [AssetController::class, 'import'])->name('import');
        Route::post('/{asset}/assign', [AssetController::class, 'assign'])->name('assign');
        Route::get('/{asset}/show', [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/barcode', [AssetController::class, 'barcode'])->name('barcode');
        Route::get('/{asset}/print-label', [AssetController::class, 'printLabel'])->name('print-label');
        Route::post('/{asset}/reassign', [AssetController::class, 'reassign'])->name('reassign');
        Route::get('/{asset}/assignments', [AssetController::class, 'getAssignments'])->name('assignments');
        Route::get('/{asset}/assignments/{assignment}/document', [AssetController::class, 'downloadAssignmentDocument'])->name('assignments.document');
        Route::post('/{asset}/unassign', [AssetController::class, 'unassign'])->name('unassign');

        //Maintenance Route
        Route::post('/{asset}/maintenance-logs', [MaintenanceLogController::class, 'store'])->name('maintenance_logs.store');
    });

    // Audit Sessions
    Route::get('/audits', [AuditSessionController::class, 'index'])->name('audits.index');
    Route::post('/audits', [AuditSessionController::class, 'store'])->name('audits.store');
    Route::get('/audits/{audit}', [AuditSessionController::class, 'show'])->name('audits.show');
    Route::post('/audits/{audit}/scan', [AuditSessionController::class, 'scan'])->name('audits.scan');
    Route::post('/audits/{audit}/close', [AuditSessionController::class, 'close'])->name('audits.close');
    Route::get('/audits/{audit}/variance', [AuditSessionController::class, 'variance'])->name('audits.variance');

    //Locations Routes
    Route::resource('settings/locations', LocationController::class)->except(['show']);
    //Categories Routes
    Route::resource('settings/categories', CategoryController::class)->except(['show']);
    //User Management Routes
    Route::resource('settings/users', UserController::class)->except(['show']);
    // Supplier routes
    Route::resource('settings/suppliers', SupplierController::class)->except(['show']);
    // Department routes
    Route::resource('settings/departments', DepartmentController::class)->except(['show']);
    // Brand routes
    Route::resource('settings/brands', BrandController::class)->except(['show']);
    // Asset Status routes
    Route::resource('settings/asset-statuses', AssetStatusController::class)->except(['show']);
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
