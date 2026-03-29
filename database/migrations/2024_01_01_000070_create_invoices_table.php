<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->decimal('tax_rate', 5, 2)->default(19.00); // MwSt in %
            $table->decimal('discount', 10, 2)->default(0); // Rabatt in €
            $table->text('notes')->nullable(); // Freitext für Rechnung
            // Snapshot der eigenen Adresse zum Zeitpunkt der Rechnungserstellung
            $table->text('sender_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_time_entry', function (Blueprint $table) {
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_entry_id')->constrained()->cascadeOnDelete();
            $table->primary(['invoice_id', 'time_entry_id']);
        });

        Schema::create('invoice_expense', function (Blueprint $table) {
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->primary(['invoice_id', 'expense_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_expense');
        Schema::dropIfExists('invoice_time_entry');
        Schema::dropIfExists('invoices');
    }
};
