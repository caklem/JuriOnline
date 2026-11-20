<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tiga keadaan penilaian peserta:
     *  - true  : dibuka manual oleh panitia (boleh lebih awal dari hari H)
     *  - false : ditutup manual oleh panitia
     *  - null  : otomatis — terbuka sejak tanggal hari H
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('scoring_enabled')->nullable()->default(null)->after('event_date');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('scoring_enabled');
        });
    }
};
