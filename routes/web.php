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
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\SupportCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\RecurringTaskController;

// ── Authentifizierung (öffentlich) ──────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Öffentliche Helpdesk-Startseite ─────────────────────────────────────────
Route::get('/', [TicketController::class, 'home'])->name('helpdesk.home');

// ── Öffentliche Helpdesk-Routen (kein Login erforderlich) ───────────────────
// Schreib-Routen: max. 10 Anfragen pro 5 Minuten pro IP
Route::middleware('throttle:10,5')->group(function () {
    Route::post('/support',                         [TicketController::class, 'store'])->name('helpdesk.store');
    Route::post('/support/track',                   [TicketController::class, 'track'])->name('helpdesk.track.post');
    Route::post('/support/ticket/{ticket}/reply',   [TicketController::class, 'reply'])->name('helpdesk.ticket.reply');
});
// Lese-Routen: offen
Route::get('/support',                              [TicketController::class, 'create'])->name('helpdesk.create');
Route::get('/support/submitted/{ticket}',           [TicketController::class, 'submitted'])->name('helpdesk.submitted');
Route::get('/support/track',                        [TicketController::class, 'trackForm'])->name('helpdesk.track');
Route::get('/support/ticket/{ticket}',              [TicketController::class, 'conversation'])->name('helpdesk.conversation');

// ── Alle geschützten Routen (Verwaltung unter /admin/) ───────────────────────
Route::middleware('auth.simple')->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kunden
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/send-message', [CustomerController::class, 'sendMessage'])->name('customers.send-message');

    // Projekte
    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{project}/tasks-json', [ProjectController::class, 'tasksJson'])->name('projects.tasks-json');
    Route::post('/projects/{project}/files', [\App\Http\Controllers\ProjectFileController::class, 'store'])->name('project-files.store');
    Route::delete('/project-files/{file}', [\App\Http\Controllers\ProjectFileController::class, 'destroy'])->name('project-files.destroy');
    Route::get('/project-files/{file}/download', [\App\Http\Controllers\ProjectFileController::class, 'download'])->name('project-files.download');

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
    Route::get('/contracts/{contract}/download-pdf',  [ContractController::class, 'downloadPdf'])->name('contracts.download-pdf');
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

    // Helpdesk (Admin)
    Route::get('/helpdesk',                             [HelpdeskController::class, 'index'])->name('helpdesk.index');
    Route::post('/helpdesk',                            [HelpdeskController::class, 'adminStore'])->name('helpdesk.admin-store');
    Route::get('/helpdesk/{ticket}',                    [HelpdeskController::class, 'show'])->name('helpdesk.show');
    Route::post('/helpdesk/{ticket}/reply',             [HelpdeskController::class, 'reply'])->name('helpdesk.reply');
    Route::patch('/helpdesk/{ticket}/status',           [HelpdeskController::class, 'updateStatus'])->name('helpdesk.status');
    Route::post('/helpdesk/{ticket}/create-task',       [HelpdeskController::class, 'createTask'])->name('helpdesk.create-task');
    Route::delete('/helpdesk/{ticket}',                 [HelpdeskController::class, 'destroy'])->name('helpdesk.destroy');

    // Kunden SLA-Zeiten
    Route::put('/customers/{customer}/sla',             [CustomerController::class, 'updateSla'])->name('customers.sla.update');

    // Admin-only Routen
    Route::middleware('ensure.admin')->group(function () {
        Route::post('/settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/settings/customer-message', [SettingController::class, 'updateCustomerMessageTemplate'])->name('settings.customer-message');

        // Team-Verwaltung
        Route::resource('team', TeamController::class)
            ->parameters(['team' => 'team'])
            ->only(['index', 'create', 'store', 'edit', 'update']);

        // Support-Kategorien
        Route::resource('support-categories', SupportCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Export / Import
        Route::get('/export',          [ExportImportController::class, 'showExport'])->name('export-import.export');
        Route::get('/export/download', [ExportImportController::class, 'export'])->name('export-import.download');
        Route::get('/import',          [ExportImportController::class, 'showImport'])->name('export-import.import');
        Route::post('/import',         [ExportImportController::class, 'import'])->name('export-import.import.post');
    });

});
