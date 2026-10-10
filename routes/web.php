<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));

// Guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::get('customers/{customer}/delete', [CustomerController::class, 'delete'])->name('customers.delete');
    Route::resource('customers', CustomerController::class)->except('show');

    // Products
    Route::get('products/{product}/delete', [ProductController::class, 'delete'])->name('products.delete');
    Route::resource('products', ProductController::class)->except('show');

    // Billing (invoices)
    Route::get('billings/{billing}/delete', [BillingController::class, 'delete'])->name('billings.delete');
    Route::resource('billings', BillingController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('billings/{billing}/payments/create', [PaymentController::class, 'create'])->name('billings.payments.create');
    Route::post('billings/{billing}/payments', [PaymentController::class, 'store'])->name('billings.payments.store');
    Route::get('payments/{payment}/delete', [PaymentController::class, 'delete'])->name('payments.delete');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Reports & invoices (HTML + PDF)
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'downloadReport'])->name('reports.pdf');
    Route::get('billings/{billing}/invoice', [ReportController::class, 'invoice'])->name('invoices.show');
    Route::get('billings/{billing}/invoice/pdf', [ReportController::class, 'downloadInvoice'])->name('invoices.pdf');

    // Database backups (pg_dump)
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
    Route::get('backups/{file}', [BackupController::class, 'download'])->where('file', '[A-Za-z0-9._-]+\.sql')->name('backups.download');
    Route::delete('backups/{file}', [BackupController::class, 'destroy'])->where('file', '[A-Za-z0-9._-]+\.sql')->name('backups.destroy');
});
