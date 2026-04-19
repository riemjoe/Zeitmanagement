<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_waits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();

            // Zustand zum Zeitpunkt des Wartens
            $table->text('trigger_context');       // JSON: Ursprungskontext (trigger.*)
            $table->text('accumulated_variables'); // JSON: $this->variables zum Zeitpunkt des Wait
            $table->text('remaining_steps');       // JSON: Schritte die nach dem Wait noch folgen

            // Bedingung die geprüft wird
            $table->string('condition_model');     // z.B. 'Project'
            $table->string('condition_id');        // bereits aufgelöster Wert der ID
            $table->string('condition_field');     // z.B. 'status'
            $table->string('condition_operator');  // =, !=, >, <, >=, <=, contains, not_contains
            $table->string('condition_value');     // z.B. 'approved'

            // Zeitplanung
            $table->unsignedInteger('check_interval_minutes')->default(5);
            $table->timestamp('next_check_at');
            $table->timestamp('expires_at');       // nach dieser Zeit gilt die Automation als fehlgeschlagen

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_waits');
    }
};
