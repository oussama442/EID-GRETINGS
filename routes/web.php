<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RevenueReportExportController;
use App\Http\Controllers\PublicController;

Route::middleware('auth')->prefix('admin/revenue-reports/export')->name('admin.revenue-reports.export.')->group(function (): void {
    Route::get('/csv', [RevenueReportExportController::class, 'csv'])->name('csv');
    Route::get('/pdf', [RevenueReportExportController::class, 'pdf'])->name('pdf');
});

Route::get('/', [PublicController::class, 'home'])->name('public.home');
Route::get('/fleet', [PublicController::class, 'fleet'])->name('public.fleet');
Route::get('/fleet/{id}', [PublicController::class, 'showCar'])->name('public.car');
Route::post('/fleet/{id}/book', [PublicController::class, 'requestBooking'])->name('public.book');

Route::view('/success', 'public.success')->name('public.success');

Route::get('/lang/{lang}', [\App\Http\Controllers\LanguageController::class, 'switchLang'])->name('lang.switch');
