<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->string('status', 20)->default('published')->after('criteria_responses');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('published_at')->nullable()->after('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('evaluations_published_at')->nullable()->after('archived_at');
        });

        // Skor lama dianggap sudah terbit agar hasil historis tidak hilang.
        DB::table('jury_scores')->update([
            'status' => 'published',
            'submitted_at' => DB::raw('created_at'),
            'published_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['status', 'submitted_at', 'published_at', 'reviewed_by', 'reviewed_at', 'review_notes']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('evaluations_published_at');
        });
    }
};
