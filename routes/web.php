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
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ProjectTodoController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTemplateController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\RecurringTaskController;

// ── Authentifizierung (öffentlich) ──────────────────────────────────────────
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Alle geschützten Routen ──────────────────────────────────────────────────
Route::middleware('auth.simple')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kunden
    Route::resource('customers', CustomerController::class);

    // Projekte
    Route::resource('projects', ProjectController::class);
    // Tasks eines Projekts als JSON (für Zeiterfassungs-Selektor)
    Route::get('/projects/{project}/tasks-json', [ProjectController::class, 'tasksJson'])->name('projects.tasks-json');

    // Arbeitskategorien
    Route::resource('work-categories', WorkCategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Zeiterfassung
    Route::resource('time-entries', TimeEntryController::class)
        ->except(['show']);

    // Ausgaben
    Route::resource('expenses', ExpenseController::class)
        ->except(['show']);

    // Rechnungen
    Route::get('/invoices/billable-items', [InvoiceController::class, 'getBillableItems'])
        ->name('invoices.billable-items');
    Route::get('/invoices/{invoice}/leistungsbeschreibung', [InvoiceController::class, 'leistungsbeschreibung'])
        ->name('invoices.leistungsbeschreibung');
    Route::resource('invoices', InvoiceController::class);

    // Angebote
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convertToProject'])
        ->name('quotes.convert');
    Route::get('/quotes/{quote}/pdf',        [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::get('/quotes/{quote}/lastenheft', [QuoteController::class, 'lastenheft'])->name('quotes.lastenheft');
    Route::resource('quotes', QuoteController::class);

    // Verträge
    Route::get('/contracts/render-template',          [ContractController::class, 'renderTemplate'])->name('contracts.render-template');
    Route::post('/contracts/{contract}/upload-pdf',   [ContractController::class, 'uploadPdf'])->name('contracts.upload-pdf');
    Route::get('/contracts/{contract}/print',         [ContractController::class, 'print'])->name('contracts.print');
    Route::resource('contracts', ContractController::class);

    // Vertragsvorlagen
    Route::resource('contract-templates', ContractTemplateController::class);

    // Wiederkehrende Aufgaben
    Route::post('/projects/{project}/recurring-tasks',            [RecurringTaskController::class, 'store'])->name('recurring-tasks.store');
    Route::put('/recurring-tasks/{recurringTask}',                [RecurringTaskController::class, 'update'])->name('recurring-tasks.update');
    Route::delete('/recurring-tasks/{recurringTask}',             [RecurringTaskController::class, 'destroy'])->name('recurring-tasks.destroy');
    Route::post('/recurring-tasks/{recurringTask}/run-now',       [RecurringTaskController::class, 'runNow'])->name('recurring-tasks.run-now');

    // Kanban-Board
    Route::get('/kanban',                           [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('/kanban/tasks',                    [KanbanController::class, 'store'])->name('kanban.store');
    Route::patch('/kanban/tasks/{task}/status',     [KanbanController::class, 'updateStatus'])->name('kanban.update-status');
    Route::put('/kanban/tasks/{task}',              [KanbanController::class, 'update'])->name('kanban.update');
    Route::delete('/kanban/tasks/{task}',           [KanbanController::class, 'destroy'])->name('kanban.destroy');

    // Wartungsplan
    Route::get('/projects/{project}/maintenance',                       [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/projects/{project}/maintenance',                      [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::put('/maintenance/{maintenanceEvent}',                       [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::patch('/maintenance/{maintenanceEvent}/toggle',              [MaintenanceController::class, 'toggle'])->name('maintenance.toggle');
    Route::delete('/maintenance/{maintenanceEvent}',                    [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

    // Projekt-ToDos
    Route::post('/projects/{project}/todos',    [ProjectTodoController::class, 'store'])->name('project-todos.store');
    Route::patch('/todos/{todo}/toggle',        [ProjectTodoController::class, 'toggle'])->name('project-todos.toggle');
    Route::delete('/todos/{todo}',              [ProjectTodoController::class, 'destroy'])->name('project-todos.destroy');
    Route::post('/todos/reorder',               [ProjectTodoController::class, 'reorder'])->name('project-todos.reorder');

    // Live-Timer (nutzer-spezifisch)
    Route::get('/timer/status',   [TimerController::class, 'status'])->name('timer.status');
    Route::post('/timer/start',   [TimerController::class, 'start'])->name('timer.start');
    Route::post('/timer/stop',    [TimerController::class, 'stop'])->name('timer.stop');
    Route::post('/timer/cancel',  [TimerController::class, 'cancel'])->name('timer.cancel');
    Route::post('/timer/pause',   [TimerController::class, 'pause'])->name('timer.pause');
    Route::post('/timer/resume',  [TimerController::class, 'resume'])->name('timer.resume');

    // Einstellungen – Profil & Passwort für alle Nutzer
    Route::get('/settings',          [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile',  [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password',[SettingController::class, 'updatePassword'])->name('settings.password');

    // Einstellungen – Unternehmenseinstellungen nur für Admins
    Route::middleware('ensure.admin')->group(function () {
        Route::post('/settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Team-Verwaltung
        Route::resource('team', TeamController::class)
            ->parameters(['team' => 'team'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

    // Export / Import
    Route::get('/export',          [ExportImportController::class, 'showExport'])->name('export-import.export');
    Route::get('/export/download', [ExportImportController::class, 'export'])->name('export-import.download');
    Route::get('/import',          [ExportImportController::class, 'showImport'])->name('export-import.import');
    Route::post('/import',         [ExportImportController::class, 'import'])->name('export-import.import.post');

});
