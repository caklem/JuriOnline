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
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->foreignId('jury_form_template_id')->nullable()->after('jury_id')->constrained('jury_form_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->dropForeign(['jury_form_template_id']);
            $table->dropColumn('jury_form_template_id');
        });
    }
};
