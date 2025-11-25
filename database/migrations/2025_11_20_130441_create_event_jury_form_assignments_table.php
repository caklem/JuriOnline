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
        Schema::create('event_jury_form_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('jury_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jury_form_template_id')->constrained('jury_form_templates')->onDelete('cascade');
            $table->timestamps();

            // Satu juri hanya bisa di-assign ke satu form per event
            $table->unique(['event_id', 'jury_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_jury_form_assignments');
    }
};
