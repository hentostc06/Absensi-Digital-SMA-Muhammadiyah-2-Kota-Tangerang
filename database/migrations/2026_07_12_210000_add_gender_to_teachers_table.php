<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teachers', 'gender')) {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->string('gender', 1)->nullable()->after('niy_nbm');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teachers', 'gender')) {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->dropColumn('gender');
            });
        }
    }
};
