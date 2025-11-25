<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jury_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->foreignId('jury_id')->constrained('users')->onDelete('cascade');
            $table->decimal('score', 5, 2);
            $table->text('public_feedback')->nullable(); // Visible to participant
            $table->text('private_evaluation')->nullable(); // Only for admin/organizer
            $table->timestamps();

            $table->unique(['registration_id', 'jury_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jury_scores');
    }
};
