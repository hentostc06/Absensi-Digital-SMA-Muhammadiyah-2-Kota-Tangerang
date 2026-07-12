<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('email')->nullable()->after('username');
            });
        }

        if (! Schema::hasColumn('users', 'password_reset_sent_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('password_reset_sent_at')->nullable()->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'password_reset_sent_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('password_reset_sent_at');
            });
        }

        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('email');
            });
        }
    }
};
