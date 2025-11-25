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
            $table->text('description')->nullable()->after('criteria_fields');
        });

        Schema::table('event_forms', function (Blueprint $table) {
            $table->text('description')->nullable()->after('form_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jury_form_templates', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('event_forms', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
