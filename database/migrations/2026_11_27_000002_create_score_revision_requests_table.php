<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_revision_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jury_score_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jury_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->json('proposed_payload')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['jury_id', 'status']);
            $table->index(['jury_score_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_revision_requests');
    }
};
