<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_sessions') && ! Schema::hasColumn('attendance_sessions', 'session_duration_minutes')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->unsignedSmallInteger('session_duration_minutes')
                    ->default(15)
                    ->after('late_after_minutes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_sessions') && Schema::hasColumn('attendance_sessions', 'session_duration_minutes')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                $table->dropColumn('session_duration_minutes');
            });
        }
    }
};
