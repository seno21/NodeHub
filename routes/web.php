<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ComputerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RemoteActionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VncSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', [ComputerController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('computers/status', [ComputerController::class, 'status'])->name('computers.status');
    Route::get('computers/export', [ComputerController::class, 'export'])->name('computers.export');
    Route::post('computers/import', [ComputerController::class, 'import'])->name('computers.import');

    Route::resource('computers', ComputerController::class)->except(['show']);

    Route::get('computers/{computer}/ping', [ComputerController::class, 'ping'])
        ->name('computers.ping');

    Route::post('computers/{computer}/connect', [VncSessionController::class, 'start'])
        ->name('computers.connect');

    Route::resource('actions', RemoteActionController::class);
    Route::post('actions/{action}/execute', [RemoteActionController::class, 'execute'])->name('actions.execute');

    Route::resource('tags', TagController::class)->except(['show', 'create', 'edit']);

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::post('audit-logs/prune', [AuditLogController::class, 'prune'])->name('audit-logs.prune');

    Route::view('about', 'about')->name('about');

    Route::get('viewer/{token}', [VncSessionController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{40}')
        ->name('viewer.show');

    Route::get('vnc/ticket/{token}', [VncSessionController::class, 'ticket'])
        ->where('token', '[A-Za-z0-9]{40}')
        ->name('viewer.ticket');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
