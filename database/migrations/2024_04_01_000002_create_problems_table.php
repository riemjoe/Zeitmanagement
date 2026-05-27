<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();            // PRB-0001

            // Kern
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open');     // open, under_investigation, known_error, resolved, closed
            $table->string('priority')->default('medium'); // critical, high, medium, low
            $table->string('impact')->default('medium');   // high, medium, low
            $table->string('category')->nullable();
            $table->string('affected_service')->nullable();

            // Verknüpfungen
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Dokumentation
            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->text('resolution')->nullable();

            // Zeitstempel
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });

        // Jetzt problem_id FK auf incidents setzen, da problems-Tabelle existiert
        Schema::table('incidents', function (Blueprint $table) {
            $table->foreign('problem_id')->references('id')->on('problems')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['problem_id']);
        });
        Schema::dropIfExists('problems');
    }
};
