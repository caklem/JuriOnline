<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_jury_form_assignments', function (Blueprint $table) {
            // Label opsional: juri ini bertugas sebagai juri apa (mis. "Juri Vokal", "Juri Adab")
            $table->string('jury_role', 100)->nullable()->after('jury_form_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_jury_form_assignments', function (Blueprint $table) {
            $table->dropColumn('jury_role');
        });
    }
};
