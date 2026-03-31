<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Optionale Arbeitskategorie – wird beim Starten der Zeiterfassung vorausgefüllt.
            $table->foreignId('work_category_id')
                  ->nullable()
                  ->after('assigned_to')
                  ->constrained('work_categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['work_category_id']);
            $table->dropColumn('work_category_id');
        });
    }
};
