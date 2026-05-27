<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();           // INC-0001

            // Kern
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open');    // open, in_progress, pending, resolved, closed
            $table->string('priority')->default('medium');// critical, high, medium, low
            $table->string('impact')->default('medium');  // high, medium, low
            $table->string('urgency')->default('medium'); // high, medium, low
            $table->string('category')->nullable();
            $table->string('affected_service')->nullable();

            // Verknüpfungen
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('problem_id')->nullable();  // FK nach problems, wird in späterer Migration gesetzt
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Dokumentation
            $table->string('reported_by')->nullable();
            $table->text('workaround')->nullable();
            $table->text('resolution')->nullable();

            // SLA
            $table->dateTime('response_due_at')->nullable();
            $table->dateTime('resolve_due_at')->nullable();
            $table->dateTime('responded_at')->nullable();

            // Zeitstempel
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
