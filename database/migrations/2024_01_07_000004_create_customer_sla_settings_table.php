<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_sla_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('support_category_id')->constrained('support_categories')->cascadeOnDelete();
            $table->unsignedInteger('sla_hours'); // Response time in hours
            $table->timestamps();

            $table->unique(['customer_id', 'support_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sla_settings');
    }
};
