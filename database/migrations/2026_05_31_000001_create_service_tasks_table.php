<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();                 // TSK-0001
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('task');           // task | maintenance
            $table->string('status', 30)->default('open');         // open | in_progress | completed | cancelled
            $table->string('priority', 20)->default('medium');     // low | medium | high | critical
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            // Polymorphischer Link zum Ursprungsobjekt (Task oder MaintenanceEvent)
            $table->string('taskable_type')->nullable();
            $table->unsignedBigInteger('taskable_id')->nullable();
            $table->index(['taskable_type', 'taskable_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tasks');
    }
};

// ── Separate Migrationsklasse für Bestandsdaten-Sync ──────────────────────────
// Diese Klasse wird von Laravel nicht automatisch ausgeführt – sie ist nur zur
// Dokumentation. Der initiale Sync erfolgt über php artisan db:seed --class=ServiceTaskSeeder
// oder direkt: php artisan tinker --execute="App\Models\ServiceTask::initialSync()"
