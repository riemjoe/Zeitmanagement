<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_itil_sla_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('priority'); // critical, high, medium, low
            $table->unsignedInteger('response_hours');
            $table->unsignedInteger('resolve_hours');
            $table->timestamps();

            $table->unique(['customer_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_itil_sla_settings');
    }
};
