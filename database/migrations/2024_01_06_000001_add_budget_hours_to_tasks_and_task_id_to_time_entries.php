<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Zeitbudget (Stunden) für Aufgaben
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'budget_hours')) {
                $table->decimal('budget_hours', 8, 2)->nullable()->after('due_date');
            }
        });

        // Optionale Aufgaben-Verknüpfung für Zeiteinträge
        Schema::table('time_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('time_entries', 'task_id')) {
                $table->foreignId('task_id')
                      ->nullable()
                      ->after('work_category_id')
                      ->constrained('tasks')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn('task_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('budget_hours');
        });
    }
};
