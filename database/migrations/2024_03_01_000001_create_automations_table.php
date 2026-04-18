<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('yaml');                  // vollständige YAML-Definition
            $table->string('trigger_type');        // model_created, model_updated, ...
            $table->string('trigger_model')->nullable(); // Task, Project, ...
            $table->string('webhook_token')->nullable()->unique(); // für Webhook-Trigger
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->timestamps();
        });

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['success', 'error', 'skipped'])->default('success');
            $table->text('context')->nullable();   // JSON-kodierter Auslöse-Kontext
            $table->text('log')->nullable();       // Zeilenweise Ausführungsprotokoll
            $table->string('error_message')->nullable();
            $table->float('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automations');
    }
};
