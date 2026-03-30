<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('quote_number')->unique();
            $table->string('title');
            $table->date('date');
            $table->date('valid_until')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected'])->default('draft');
            $table->decimal('hourly_rate', 10, 2)->nullable();   // null = globaler Satz
            $table->unsignedSmallInteger('lines_per_hour')->default(50); // LoC-Schätzung
            $table->decimal('tax_rate', 5, 2)->default(19.00);
            $table->decimal('discount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('sender_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
