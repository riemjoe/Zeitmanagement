<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 11)->unique(); // XXX-XXX-XXX
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_email');
            $table->foreignId('support_category_id')->nullable()->constrained('support_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'waiting', 'closed'])->default('open');
            $table->timestamp('sla_deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
