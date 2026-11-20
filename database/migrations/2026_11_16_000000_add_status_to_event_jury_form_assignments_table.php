<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_jury_form_assignments', function (Blueprint $table) {
            // pending = menunggu persetujuan juri, accepted = juri setuju, declined = juri menolak
            $table->string('status', 20)->default('pending')->after('jury_role');
            $table->timestamp('responded_at')->nullable()->after('status');
        });

        // Assignment yang sudah ada sebelum alur persetujuan dianggap sudah disetujui,
        // supaya juri lama tidak kehilangan akses.
        DB::table('event_jury_form_assignments')->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_jury_form_assignments', function (Blueprint $table) {
            $table->dropColumn(['status', 'responded_at']);
        });
    }
};
