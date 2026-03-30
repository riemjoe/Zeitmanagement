<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WorkCategoryController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ProjectTodoController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTemplateController;

// Authentifizierung (ungeschützt)
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Alle anderen Routen nur nach Login
Route::middleware('auth.simple')->group(function () {

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

    // Rechnungen – custom-Routen VOR resource-Route definieren, sonst matcht {invoice} zuerst
    Route::get('/invoices/billable-items', [InvoiceController::class, 'getBillableItems'])
        ->name('invoices.billable-items');
    Route::get('/invoices/{invoice}/leistungsbeschreibung', [InvoiceController::class, 'leistungsbeschreibung'])
        ->name('invoices.leistungsbeschreibung');
    Route::resource('invoices', InvoiceController::class);

    // Einstellungen
    Route::get('/settings',  [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings',  [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');

    // Export / Import
    Route::get('/export',          [ExportImportController::class, 'showExport'])->name('export-import.export');
    Route::get('/export/download', [ExportImportController::class, 'export'])->name('export-import.download');
    Route::get('/import',          [ExportImportController::class, 'showImport'])->name('export-import.import');
    Route::post('/import',         [ExportImportController::class, 'import'])->name('export-import.import.post');

    // Angebote – custom-Routen VOR resource definieren
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convertToProject'])
        ->name('quotes.convert');
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'pdf'])
        ->name('quotes.pdf');
    Route::get('/quotes/{quote}/lastenheft', [QuoteController::class, 'lastenheft'])
        ->name('quotes.lastenheft');
    Route::resource('quotes', QuoteController::class);

    // Verträge – custom-Routen vor resource
    Route::get('/contracts/render-template', [ContractController::class, 'renderTemplate'])
        ->name('contracts.render-template');
    Route::post('/contracts/{contract}/upload-pdf', [ContractController::class, 'uploadPdf'])
        ->name('contracts.upload-pdf');
    Route::get('/contracts/{contract}/print', [ContractController::class, 'print'])
        ->name('contracts.print');
    Route::resource('contracts', ContractController::class);

    // Vertragsvorlagen
    Route::resource('contract-templates', ContractTemplateController::class);

    // Projekt-ToDos
    Route::post('/projects/{project}/todos', [ProjectTodoController::class, 'store'])
        ->name('project-todos.store');
    Route::patch('/todos/{todo}/toggle', [ProjectTodoController::class, 'toggle'])
        ->name('project-todos.toggle');
    Route::delete('/todos/{todo}', [ProjectTodoController::class, 'destroy'])
        ->name('project-todos.destroy');
    Route::post('/todos/reorder', [ProjectTodoController::class, 'reorder'])
        ->name('project-todos.reorder');

    // Live-Timer
    Route::get('/timer/status',  [TimerController::class, 'status'])->name('timer.status');
    Route::post('/timer/start',  [TimerController::class, 'start'])->name('timer.start');
    Route::post('/timer/stop',   [TimerController::class, 'stop'])->name('timer.stop');
    Route::post('/timer/cancel', [TimerController::class, 'cancel'])->name('timer.cancel');

});
