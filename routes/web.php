<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WorkCategoryController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Kunden
Route::resource('customers', CustomerController::class);

// Projekte
Route::resource('projects', ProjectController::class);

// Arbeitskategorien
Route::resource('work-categories', WorkCategoryController::class)
    ->only(['index', 'store', 'update', 'destroy']);

// Zeiterfassung
Route::resource('time-entries', TimeEntryController::class)
    ->except(['show']);

// Ausgaben
Route::resource('expenses', ExpenseController::class)
    ->except(['show']);

// Rechnungen – billable-items VOR resource-Route definieren, sonst matcht {invoice} zuerst
Route::get('/invoices/billable-items', [InvoiceController::class, 'getBillableItems'])
    ->name('invoices.billable-items');
Route::resource('invoices', InvoiceController::class);

// Einstellungen
Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
