<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            # Temp column zum Zwischenspeichern der alten Status-Werte
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->string('status_tmp')->default('success');
            });

            # Alte Status-Werte in die Temp-Spalte kopieren
            DB::statement("UPDATE automation_logs SET status_tmp = status");

            # Altes Status-Feld entfernen und neues ENUM-Feld mit erweitertem Wertebereich hinzufügen
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            # Neues ENUM-Feld mit erweitertem Wertebereich hinzufügen
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->enum('status', ['success', 'error', 'skipped', 'waiting'])->default('success');
            });

            # Alte Status-Werte aus der Temp-Spalte zurück in das neue Status-Feld kopieren
            DB::statement("UPDATE automation_logs SET status = status_tmp");

            # Temp-Spalte entfernen
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            # Temp-Spalte zum Zwischenspeichern der aktuellen Status-Werte
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->string('status_tmp')->default('success');
            });

            # Aktuelle Status-Werte in die Temp-Spalte kopieren
            DB::statement("UPDATE automation_logs SET status_tmp = status");

            # Altes ENUM-Feld entfernen und neues ENUM-Feld mit ursprünglichem Wertebereich hinzufügen
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            # Neues ENUM-Feld mit ursprünglichem Wertebereich hinzufügen
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->enum('status', ['success', 'error', 'skipped'])->default('success');
            });

            # Aktuelle Status-Werte aus der Temp-Spalte zurück in das neue Status-Feld kopieren
            DB::statement("UPDATE automation_logs SET status = status_tmp");

            # Temp-Spalte entfernen
            Schema::table('automation_logs', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });
        });
    }
};
