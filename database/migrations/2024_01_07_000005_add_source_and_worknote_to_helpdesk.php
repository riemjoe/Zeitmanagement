<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('source')->default('Helpdesk')->after('description');
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->boolean('is_worknote')->default(false)->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('source');
        });
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropColumn('is_worknote');
        });
    }
};
