<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->string('ticket_id', 100)->nullable()->after('description');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->text('service_description')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn('ticket_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('service_description');
        });
    }
};
