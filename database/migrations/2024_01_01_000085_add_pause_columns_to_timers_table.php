<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            if (! Schema::hasColumn('timers', 'paused_at')) {
                $table->dateTime('paused_at')->nullable()->after('description');
            }
            if (! Schema::hasColumn('timers', 'paused_seconds')) {
                $table->unsignedInteger('paused_seconds')->default(0)->after('paused_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'paused_seconds']);
        });
    }
};
