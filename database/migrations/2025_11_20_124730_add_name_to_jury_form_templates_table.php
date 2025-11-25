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
        Schema::table('jury_form_templates', function (Blueprint $table) {
            $table->string('name')->nullable()->after('event_id'); // Nama form: Juri Vokal, Juri Adab, Juri Terbang, dll
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jury_form_templates', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
