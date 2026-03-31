<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            // Geplantes Datum und optionale Uhrzeit
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();

            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');

            // null = einmalig, recurring_task_id = aus Vorlage erzeugt
            $table->foreignId('recurring_task_id')
                  ->nullable()
                  ->constrained('recurring_tasks')
                  ->nullOnDelete();

            // Erledigt-Status
            $table->boolean('is_done')->default(false);
            $table->timestamp('done_at')->nullable();

            // E-Mail-Benachrichtigung
            $table->boolean('notify')->default(true);
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_events');
    }
};
