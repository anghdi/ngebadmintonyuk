<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PlaySessionController;
use App\Http\Controllers\PublicPlaySessionController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionRegistrationController;
use App\Http\Controllers\ShuttlecockInventoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TopUpRequestController;
use App\Http\Controllers\TopUpSettingController;
use App\Http\Controllers\TransactionController;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');
Route::get('/app.css', fn () => response()->file(resource_path('css/app.css'), ['Content-Type' => 'text/css']))->name('app.css');
Route::get('/jadwal', [PublicPlaySessionController::class, 'index'])->name('public-sessions.index');
Route::get('/jadwal/{playSession}', [PublicPlaySessionController::class, 'show'])->name('public-sessions.show');
Route::post('/jadwal/{playSession}/daftar', [SessionRegistrationController::class, 'store'])->middleware('throttle:5,1')->name('public-sessions.register');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/daftar', [RegistrationController::class, 'create'])->name('register');
    Route::post('/daftar', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
});

Route::bind('transaction', fn ($id) => request()->routeIs('incomes.*') ? Income::findOrFail($id) : Expense::findOrFail($id));
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/top-ups', [TopUpRequestController::class, 'index'])->name('top-ups.index');
    Route::post('/top-ups', [TopUpRequestController::class, 'store'])->middleware('throttle:5,1')->name('top-ups.store');
    Route::get('/top-ups/{topUpRequest}/proof', [TopUpRequestController::class, 'proof'])->name('top-ups.proof');

    Route::middleware('can:admin')->group(function () {
        Route::put('/top-up-settings', [TopUpSettingController::class, 'update'])->name('top-up-settings.update');
        Route::put('/top-ups/{topUpRequest}', [TopUpRequestController::class, 'update'])->name('top-ups.update');
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        foreach (['incomes', 'expenses'] as $uri) {
            Route::resource($uri, TransactionController::class)->parameters([$uri => 'transaction']);
        }
        Route::get('/reports', ReportController::class)->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'download'])->name('reports.pdf');
        Route::resource('members', MemberController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('/members/{member}/memberships', [MembershipController::class, 'store'])->name('memberships.store');
        Route::put('/members/{member}/memberships/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
        Route::delete('/members/{member}/memberships/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');
        Route::resource('play-sessions', PlaySessionController::class)->only(['index', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::put('/play-sessions/{playSession}/members/{member}/attendance', [AttendanceController::class, 'update'])->name('attendances.update');
        Route::put('/play-sessions/{playSession}/registrations/{registration}', [SessionRegistrationController::class, 'update'])->scopeBindings()->name('session-registrations.update');
        Route::get('/inventory', [ShuttlecockInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/items', [ShuttlecockInventoryController::class, 'store'])->name('inventory.items.store');
        Route::put('/inventory/items/{shuttlecockItem}', [ShuttlecockInventoryController::class, 'update'])->name('inventory.items.update');
        Route::delete('/inventory/items/{shuttlecockItem}', [ShuttlecockInventoryController::class, 'destroy'])->name('inventory.items.destroy');
        Route::post('/inventory/movements', [StockMovementController::class, 'store'])->name('inventory.movements.store');
    });
});
