<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();            // CHG-0001

            // Kern
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');    // draft, submitted, in_progress, completed, cancelled
            $table->string('type')->default('normal');     // normal, standard, emergency
            $table->string('priority')->default('medium'); // critical, high, medium, low
            $table->string('impact')->default('medium');   // high, medium, low
            $table->string('risk')->default('medium');     // high, medium, low
            $table->string('category')->nullable();
            $table->string('affected_service')->nullable();

            // Verknüpfungen
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_by')->nullable();

            // Planung
            $table->dateTime('planned_start_at')->nullable();
            $table->dateTime('planned_end_at')->nullable();
            $table->dateTime('actual_start_at')->nullable();
            $table->dateTime('actual_end_at')->nullable();

            // Dokumentationspläne
            $table->text('implementation_plan')->nullable();
            $table->text('rollback_plan')->nullable();
            $table->text('test_plan')->nullable();

            // Post-Implementation Review
            $table->text('post_review')->nullable();

            // Zeitstempel
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changes');
    }
};
