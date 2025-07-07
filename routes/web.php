<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //Assets Routes
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.delete');
    Route::post('/assets/bulk-delete', [AssetController::class, 'bulkDestroy'])->name('assets.bulkDelete');
    Route::get('/assets/export', [AssetController::class, 'export'])->name('assets.export');
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');

    //Locations Routes
    Route::resource('settings/locations', LocationController::class)->except(['show']);
    //Categories Routes
    Route::resource('settings/categories', CategoryController::class)->except(['show']);
    //User Management Routes
    Route::resource('settings/users', UserController::class)->except(['show']);
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
