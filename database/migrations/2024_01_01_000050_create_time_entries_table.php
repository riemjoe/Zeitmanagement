<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_category_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->decimal('hours', 6, 2); // z.B. 1.5 = 1h30min
            $table->text('description')->nullable();
            $table->boolean('billed')->default(false); // bereits abgerechnet?
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
