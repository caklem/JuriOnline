<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen juknis (petunjuk teknis) yang bisa dilihat peserta sebelum mendaftar.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('juknis_file')->nullable()->after('banner');
            $table->string('juknis_name')->nullable()->after('juknis_file');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['juknis_file', 'juknis_name']);
        });
    }
};
