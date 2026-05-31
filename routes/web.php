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
use App\Http\Controllers\SurveyTemplateController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\PublicSurveyController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\ProjectMessageController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\DunningController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ItilChangeController;
use App\Http\Controllers\ItilWebhookController;
use App\Http\Controllers\LeistungsberichtController;
use App\Http\Controllers\PublicWebhookController;
use App\Http\Controllers\WebhookController;

// ── Authentifizierung (öffentlich) ──────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── 2FA-Verifikation (nach Login, vor Dashboard-Zugang) ─────────────────────
Route::get('/2fa/verify',  [TwoFactorController::class, 'showVerify'])->name('2fa.verify');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post')->middleware('throttle:10,1');

// ── Öffentliche Helpdesk-Startseite ─────────────────────────────────────────
Route::get('/', [TicketController::class, 'home'])->name('helpdesk.home');

// ── Kunden-Portal ────────────────────────────────────────────────────────────
Route::prefix('portal')->name('portal.')->group(function () {
    // Öffentliche Portal-Routen
    Route::get('/login',  [CustomerPortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerPortalController::class, 'login'])->middleware('throttle:10,5')->name('login.post');
    Route::post('/logout', [CustomerPortalController::class, 'logout'])->name('logout');
    Route::get('/invitation/{token}', [CustomerPortalController::class, 'acceptInvitation'])->name('invitation');

    // Passwortänderung (nur session-auth, ohne volle Portal-Auth da must_change_password gesetzt sein kann)
    Route::middleware(\App\Http\Middleware\CustomerPortalAuth::class)->group(function () {
        Route::get('/change-password',  [CustomerPortalController::class, 'showChangePassword'])->name('change-password');
        Route::post('/change-password', [CustomerPortalController::class, 'changePassword'])->name('change-password.post');

        // 2FA
        Route::get('/2fa/prompt',   [CustomerPortalController::class, 'show2faPrompt'])->name('2fa.prompt');
        Route::get('/2fa/setup',    [CustomerPortalController::class, 'show2faSetup'])->name('2fa.setup');
        Route::post('/2fa/setup',   [CustomerPortalController::class, 'confirm2faSetup'])->name('2fa.setup.confirm');
        Route::get('/2fa/backup-codes', [CustomerPortalController::class, 'showBackupCodes'])->name('2fa.backup-codes');
        Route::get('/2fa/verify',   [CustomerPortalController::class, 'show2faVerify'])->name('2fa.verify');
        Route::post('/2fa/verify',  [CustomerPortalController::class, 'verify2fa'])->middleware('throttle:10,5')->name('2fa.verify.post');

        // Portal-Hauptseiten
        Route::get('/dashboard',     [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/projects',      [CustomerPortalController::class, 'projects'])->name('projects');
        Route::get('/tickets',       [CustomerPortalController::class, 'tickets'])->name('tickets');
        Route::get('/tickets/{ticket}', [CustomerPortalController::class, 'ticket'])->name('ticket');
        Route::get('/invoices',      [CustomerPortalController::class, 'invoices'])->name('invoices');
        Route::get('/incidents',     [CustomerPortalController::class, 'incidents'])->name('incidents');
        Route::get('/problems',      [CustomerPortalController::class, 'problems'])->name('problems');
        Route::get('/changes',       [CustomerPortalController::class, 'changes'])->name('changes');
        Route::get('/time-entries',  [CustomerPortalController::class, 'timeEntries'])->name('time-entries');
    });
});

// ── Öffentliche Umfragen (kein Login erforderlich) ──────────────────────────
Route::get('/survey/{token}',  [PublicSurveyController::class, 'show'])->name('survey.show');
Route::post('/survey/{token}', [PublicSurveyController::class, 'submit'])->name('survey.submit')
    ->middleware('throttle:20,5');

// ── Automation-Webhooks (öffentlich, Token-gesichert) ────────────────────────
Route::post('/webhook/{token}', [AutomationController::class, 'webhook'])->name('automations.webhook')
    ->middleware('throttle:60,1');

// ── ITIL-Webhooks (öffentlich, Bearer-Token-gesichert) ───────────────────────
Route::prefix('api/itil')->name('itil.api.')->middleware('throttle:60,1')->group(function () {
    Route::post('/incidents', [ItilWebhookController::class, 'createIncident'])->name('incidents');
    Route::post('/problems',  [ItilWebhookController::class, 'createProblem'])->name('problems');
    Route::post('/changes',   [ItilWebhookController::class, 'createChange'])->name('changes');
});

// ── Öffentliche Webhooks (Ticket, Kunde, Projekt – Bearer-Token-gesichert) ───
Route::prefix('api/webhooks')->name('api.webhooks.')->middleware('throttle:60,1')->group(function () {
    Route::post('/tickets',   [PublicWebhookController::class, 'createTicket'])->name('tickets');
    Route::post('/customers', [PublicWebhookController::class, 'createCustomer'])->name('customers');
    Route::post('/projects',  [PublicWebhookController::class, 'createProject'])->name('projects');
});

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
    Route::post('/projects/{project}/archive',   [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{project}/unarchive', [ProjectController::class, 'unarchive'])->name('projects.unarchive');
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
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.pdf');
    Route::get('/invoices/{invoice}/leistungsbericht-pdf', [InvoiceController::class, 'downloadLeistungsberichtPdf'])
        ->name('invoices.leistungsbericht-pdf');
    Route::resource('invoices', InvoiceController::class);

    // Leistungsberichte (standalone + rechnungsgebunden)
    Route::get('/leistungsberichte',           [LeistungsberichtController::class, 'index'])->name('leistungsberichte.index');
    Route::get('/leistungsberichte/create',    [LeistungsberichtController::class, 'create'])->name('leistungsberichte.create');
    Route::post('/leistungsberichte',          [LeistungsberichtController::class, 'store'])->name('leistungsberichte.store');
    Route::get('/leistungsberichte/{leistungsbericht}', [LeistungsberichtController::class, 'show'])->name('leistungsberichte.show');
    Route::delete('/leistungsberichte/{leistungsbericht}', [LeistungsberichtController::class, 'destroy'])->name('leistungsberichte.destroy');

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

    // Aufgaben-Kommentare
    Route::get('/kanban/tasks/{task}/comments',    [TaskCommentController::class, 'index'])->name('task-comments.index');
    Route::post('/kanban/tasks/{task}/comments',   [TaskCommentController::class, 'store'])->name('task-comments.store');
    Route::delete('/task-comments/{comment}',      [TaskCommentController::class, 'destroy'])->name('task-comments.destroy');

    // Meilensteine
    Route::post('/projects/{project}/milestones',         [MilestoneController::class, 'store'])->name('milestones.store');
    Route::patch('/milestones/{milestone}/toggle',        [MilestoneController::class, 'toggle'])->name('milestones.toggle');
    Route::delete('/milestones/{milestone}',              [MilestoneController::class, 'destroy'])->name('milestones.destroy');

    // Globale Suche
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Gantt & Burndown
    Route::get('/projects/{project}/gantt',    [ProjectController::class, 'gantt'])->name('projects.gantt');
    Route::get('/projects/{project}/burndown', [ProjectController::class, 'burndown'])->name('projects.burndown');

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

    // 2FA-Verwaltung (für eingeloggte Nutzer)
    Route::get('/2fa/setup',              [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/setup/confirm',     [TwoFactorController::class, 'confirmSetup'])->name('2fa.setup.confirm');
    Route::post('/2fa/disable',           [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/backup-codes/regenerate', [TwoFactorController::class, 'regenerateBackupCodes'])->name('2fa.backup-codes.regenerate');

    // Helpdesk (Admin)
    Route::get('/helpdesk',                             [HelpdeskController::class, 'index'])->name('helpdesk.index');
    Route::post('/helpdesk',                            [HelpdeskController::class, 'adminStore'])->name('helpdesk.admin-store');
    Route::get('/helpdesk/{ticket}',                    [HelpdeskController::class, 'show'])->name('helpdesk.show');
    Route::post('/helpdesk/{ticket}/reply',             [HelpdeskController::class, 'reply'])->name('helpdesk.reply');
    Route::patch('/helpdesk/{ticket}/status',           [HelpdeskController::class, 'updateStatus'])->name('helpdesk.status');
    Route::post('/helpdesk/{ticket}/create-task',       [HelpdeskController::class, 'createTask'])->name('helpdesk.create-task');
    Route::patch('/helpdesk/{ticket}/assign',           [HelpdeskController::class, 'assign'])->name('helpdesk.assign');
    Route::delete('/helpdesk/{ticket}',                 [HelpdeskController::class, 'destroy'])->name('helpdesk.destroy');

    // Kunden SLA-Zeiten
    Route::put('/customers/{customer}/sla',             [CustomerController::class, 'updateSla'])->name('customers.sla.update');
    Route::put('/customers/{customer}/itil-sla',        [CustomerController::class, 'updateItilSla'])->name('customers.itil-sla.update');

    // Kunden-Portal Verwaltung (Admin)
    Route::post('/customers/{customer}/portal/enable',            [CustomerPortalController::class, 'adminEnablePortal'])->name('customers.portal.enable');
    Route::post('/customers/{customer}/portal/disable',           [CustomerPortalController::class, 'adminDisablePortal'])->name('customers.portal.disable');
    Route::post('/customers/{customer}/portal/reset-password',    [CustomerPortalController::class, 'adminResetPortalPassword'])->name('customers.portal.reset-password');
    Route::post('/customers/{customer}/portal/resend-invitation', [CustomerPortalController::class, 'adminResendInvitation'])->name('customers.portal.resend-invitation');

    // Projekt-Nachrichten (Chat)
    Route::get('/projects/{project}/messages',      [ProjectMessageController::class, 'index'])->name('project-messages.index');
    Route::post('/projects/{project}/messages',     [ProjectMessageController::class, 'store'])->name('project-messages.store');
    Route::delete('/project-messages/{message}',    [ProjectMessageController::class, 'destroy'])->name('project-messages.destroy');

    // Bewertungssystem – Fragebögen
    Route::resource('survey-templates', SurveyTemplateController::class);

    // Bewertungssystem – Umfragen
    Route::get('/surveys/global', [SurveyController::class, 'globalStats'])->name('surveys.global');
    Route::get('/surveys/{survey}/responses/{response}', [SurveyController::class, 'showResponse'])->name('surveys.responses.show');
    Route::delete('/surveys/{survey}/responses/{response}', [SurveyController::class, 'destroyResponse'])->name('surveys.responses.destroy');
    Route::resource('surveys', SurveyController::class);

    // Automatisierungen
    Route::resource('automations', AutomationController::class);
    Route::patch('/automations/{automation}/toggle',      [AutomationController::class, 'toggle'])->name('automations.toggle');
    Route::post('/automations/{automation}/test',         [AutomationController::class, 'test'])->name('automations.test');
    Route::get('/automations/{automation}/export-yaml',   [AutomationController::class, 'exportYaml'])->name('automations.export-yaml');
    Route::get('/automations/{automation}/logs',          [AutomationController::class, 'logs'])->name('automations.logs');

    // ── API-Token-Verwaltung (vor resource registrieren für korrekte Priorität) ─
    Route::post('/webhooks/tokens',                          [WebhookController::class, 'storeToken'])->name('webhooks.tokens.store');
    Route::delete('/webhooks/tokens/{webhookToken}',         [WebhookController::class, 'destroyToken'])->name('webhooks.tokens.destroy');
    Route::patch('/webhooks/tokens/{webhookToken}/toggle',   [WebhookController::class, 'toggleToken'])->name('webhooks.tokens.toggle');

    // ── Webhooks (Automation-Webhooks + Hub) ─────────────────────────────────
    Route::resource('webhooks', WebhookController::class);
    Route::post('/webhooks/{webhook}/regenerate-token', [WebhookController::class, 'regenerateToken'])->name('webhooks.regenerate-token');

    // Mahnwesen
    Route::get('/dunning',                              [DunningController::class, 'index'])->name('dunning.index');
    Route::post('/dunning/{invoice}/reminder',          [DunningController::class, 'sendReminder'])->name('dunning.reminder');
    Route::post('/dunning/{invoice}/notice',            [DunningController::class, 'sendDunning'])->name('dunning.notice');

    // ── ITIL ──────────────────────────────────────────────────────────────────

    // Incidents
    Route::prefix('itil/incidents')->name('itil.incidents.')->group(function () {
        Route::get('/',                                [IncidentController::class, 'index'])->name('index');
        Route::get('/create',                          [IncidentController::class, 'create'])->name('create');
        Route::post('/',                               [IncidentController::class, 'store'])->name('store');
        Route::get('/{incident}',                      [IncidentController::class, 'show'])->name('show');
        Route::get('/{incident}/edit',                 [IncidentController::class, 'edit'])->name('edit');
        Route::put('/{incident}',                      [IncidentController::class, 'update'])->name('update');
        Route::delete('/{incident}',                   [IncidentController::class, 'destroy'])->name('destroy');
        Route::post('/{incident}/link-problem',        [IncidentController::class, 'linkProblem'])->name('link-problem');
    });

    // Problems
    Route::prefix('itil/problems')->name('itil.problems.')->group(function () {
        Route::get('/',                                    [ProblemController::class, 'index'])->name('index');
        Route::get('/create',                              [ProblemController::class, 'create'])->name('create');
        Route::post('/',                                   [ProblemController::class, 'store'])->name('store');
        Route::get('/{problem}',                           [ProblemController::class, 'show'])->name('show');
        Route::get('/{problem}/edit',                      [ProblemController::class, 'edit'])->name('edit');
        Route::put('/{problem}',                           [ProblemController::class, 'update'])->name('update');
        Route::delete('/{problem}',                        [ProblemController::class, 'destroy'])->name('destroy');
        Route::post('/{problem}/attach-incident',          [ProblemController::class, 'attachIncident'])->name('attach-incident');
        Route::delete('/{problem}/detach-incident/{incident}', [ProblemController::class, 'detachIncident'])->name('detach-incident');
    });

    // Changes
    Route::prefix('itil/changes')->name('itil.changes.')->group(function () {
        Route::get('/',                                [ItilChangeController::class, 'index'])->name('index');
        Route::get('/create',                          [ItilChangeController::class, 'create'])->name('create');
        Route::post('/',                               [ItilChangeController::class, 'store'])->name('store');
        Route::get('/{itilChange}',                    [ItilChangeController::class, 'show'])->name('show');
        Route::get('/{itilChange}/edit',               [ItilChangeController::class, 'edit'])->name('edit');
        Route::put('/{itilChange}',                    [ItilChangeController::class, 'update'])->name('update');
        Route::delete('/{itilChange}',                 [ItilChangeController::class, 'destroy'])->name('destroy');
    });

    // Ticket → ITIL Konvertierung
    Route::post('/tickets/{ticket}/convert-to-incident', [IncidentController::class, 'convertFromTicket'])->name('itil.incidents.convert-from-ticket');
    Route::post('/tickets/{ticket}/convert-to-change',   [ItilChangeController::class, 'convertFromTicket'])->name('itil.changes.convert-from-ticket');

    // Webhooks
    Route::resource('webhooks', \App\Http\Controllers\WebhookController::class);
    Route::post('/webhooks/{webhook}/regenerate-token',   [\App\Http\Controllers\WebhookController::class, 'regenerateToken'])->name('webhooks.regenerate-token');

    // Admin-only Routen
    Route::middleware('ensure.admin')->group(function () {
        Route::post('/settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/settings/customer-message', [SettingController::class, 'updateCustomerMessageTemplate'])->name('settings.customer-message');
        Route::put('/settings/dunning', [SettingController::class, 'updateDunning'])->name('settings.dunning');

        // Team-Verwaltung
        Route::resource('team', TeamController::class)
            ->parameters(['team' => 'team'])
            ->only(['index', 'create', 'store', 'edit', 'update']);

        // Support-Kategorien
        Route::resource('support-categories', SupportCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Export / Import (kombinierte Ansicht)
        Route::get('/export-import',   [ExportImportController::class, 'index'])->name('export-import.index');
        Route::get('/export/download', [ExportImportController::class, 'export'])->name('export-import.download');
        Route::post('/import',         [ExportImportController::class, 'import'])->name('export-import.import.post');
        // Legacy-Redirects für alte Lesezeichen
        Route::get('/export',          fn() => redirect()->route('export-import.index'))->name('export-import.export');
        Route::get('/import',          fn() => redirect()->route('export-import.index'))->name('export-import.import');

        // Test-Suite Dashboard (nur Admin)
        Route::get('/tests',     [TestController::class, 'index'])->name('tests.index');
        Route::get('/tests/run', [TestController::class, 'run'])->name('tests.run');
    });

});
