<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tanggal hari H (pelaksanaan utama) dipakai sebagai acuan jadwal event dan
     * masa aktif paket per event (1 bulan sejak hari H).
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('registration_end');
        });

        // Event lama belum punya hari H: pakai tanggal mulai event sebagai acuan.
        DB::table('events')
            ->whereNull('event_date')
            ->update(['event_date' => DB::raw('event_start')]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('event_date');
        });
    }
};
