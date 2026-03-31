<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recurring_tasks')) {
            return;
        }

        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('kanban_status', ['ready', 'wip', 'testing', 'completed'])->default('ready');

            // Wiederholungsrhythmus
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->unsignedTinyInteger('frequency_interval')->default(1); // alle X Tage/Wochen/Monate
            $table->unsignedTinyInteger('day_of_week')->nullable();        // 0=So … 6=Sa (für weekly)
            $table->unsignedTinyInteger('day_of_month')->nullable();       // 1–28 (für monthly)

            // Fälligkeitsversatz: N Tage nach Erstellungsdatum
            $table->unsignedSmallInteger('due_days_offset')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_tasks');
    }
};
