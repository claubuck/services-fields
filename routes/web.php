<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\LiquidationController;
use App\Http\Controllers\LiquidationModalityController;
use App\Http\Controllers\PayrollReceiptController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('establishments', EstablishmentController::class)->names('establishments');
    Route::post('employees/buscar-por-dni', [EmployeeController::class, 'buscarPorDni'])->name('employees.buscar-por-dni');
    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeController::class)->names('employees');
    Route::resource('categories', CategoryController::class)->names('categories');

    Route::get('empleador', [EmployerController::class, 'edit'])->name('employer.edit');
    Route::put('empleador', [EmployerController::class, 'update'])->name('employer.update');

    Route::resource('liquidations', LiquidationController::class)->only(['index', 'create', 'store', 'show', 'destroy'])->names('liquidations');
    Route::resource('modalidades', LiquidationModalityController::class)->parameters(['modalidades' => 'modalidad'])->names('modalidades');
    Route::get('liquidations/{liquidation}/recibos/crear', [PayrollReceiptController::class, 'create'])->name('payroll-receipts.create');
    Route::post('recibos', [PayrollReceiptController::class, 'store'])->name('payroll-receipts.store');
    Route::get('recibos/{payroll_receipt}/editar', [PayrollReceiptController::class, 'edit'])->name('payroll-receipts.edit');
    Route::put('recibos/{payroll_receipt}', [PayrollReceiptController::class, 'update'])->name('payroll-receipts.update');
    Route::delete('recibos/{payroll_receipt}', [PayrollReceiptController::class, 'destroy'])->name('payroll-receipts.destroy');
    Route::get('recibos/{payroll_receipt}/imprimir', [PayrollReceiptController::class, 'print'])->name('payroll-receipts.print');
});

require __DIR__.'/auth.php';
