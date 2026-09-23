<?php

use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('portal.enabled')->group(function () {
    Route::get('/', [PortalController::class, 'home'])->name('portal.home');
    Route::get('/propiedades', [PortalController::class, 'index'])->name('portal.index');
    Route::post('/consulta', [PortalController::class, 'inquire'])
        ->middleware('throttle:10,1')
        ->name('portal.inquire');
    Route::get('/propiedades/{property}', [PortalController::class, 'show'])->name('portal.show');
});

Route::middleware([\Filament\Http\Middleware\Authenticate::class])->prefix('admin/reports')->name('admin.reports.')->group(function () {
    Route::get('/monthly-metrics', [\App\Http\Controllers\Admin\MetricsReportController::class, 'download'])
        ->name('monthly-metrics');
});
