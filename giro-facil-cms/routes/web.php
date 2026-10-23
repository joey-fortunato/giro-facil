<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SitePageController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', [AuthController::class, 'create'])->middleware('guest')->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'store'])->middleware(['guest', 'throttle:6,1'])->name('admin.login.store');
Route::post('/agendamentos', [AppointmentController::class, 'store'])->middleware('throttle:10,1')->name('appointments.store');
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [CmsController::class, 'dashboard'])->name('dashboard');
    Route::get('/pages', [CmsController::class, 'pages'])->name('pages.index');
    Route::get('/agendamentos', [CmsController::class, 'appointments'])->name('appointments.index');
    Route::patch('/agendamentos/{appointment}', [CmsController::class, 'updateAppointment'])->name('appointments.update');
    Route::get('/contactos', [CmsController::class, 'contacts'])->name('contacts');
    Route::put('/contactos', [CmsController::class, 'updateContacts'])->name('contacts.update');
    Route::get('/pages/create', [CmsController::class, 'createPage'])->name('pages.create');
    Route::post('/pages', [CmsController::class, 'storePage'])->name('pages.store');
    Route::get('/pages/{page}/edit', [CmsController::class, 'editPage'])->name('pages.edit');
    Route::put('/pages/{page}', [CmsController::class, 'updatePage'])->name('pages.update');
    Route::post('/pages/{page}/sections', [CmsController::class, 'createSection'])->name('sections.create');
    Route::post('/pages/{page}/sections/reorder', [CmsController::class, 'reorderSections'])->name('sections.reorder');
    Route::put('/sections/{section}', [CmsController::class, 'updateSection'])->name('sections.update');
    Route::delete('/sections/{section}', [CmsController::class, 'deleteSection'])->name('sections.delete');
    Route::get('/settings', [CmsController::class, 'settings'])->name('settings');
    Route::put('/settings', [CmsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/media', [CmsController::class, 'uploadMedia'])->name('media.upload');
    Route::delete('/media/{asset}', [CmsController::class, 'deleteMedia'])->name('media.delete');
});

Route::get('/', [SitePageController::class, 'show'])->defaults('path', '')->name('site.home');
Route::get('/{path}', [SitePageController::class, 'show'])->where('path', '.*')->name('site.page');
