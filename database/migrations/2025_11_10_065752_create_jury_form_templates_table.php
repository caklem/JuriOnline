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
        Schema::create('jury_form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->json('criteria_fields'); // Kriteria penilaian dalam JSON
            $table->boolean('use_default_score')->default(true); // Tetap ada nilai 0-100
            $table->timestamps();
        });

        // Update tabel jury_scores untuk menambahkan kolom criteria_responses
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->json('criteria_responses')->nullable()->after('private_evaluation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->dropColumn('criteria_responses');
        });

        Schema::dropIfExists('jury_form_templates');
    }
};
