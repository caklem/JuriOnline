<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jury_scores', function (Blueprint $table) {
            $table->json('form_snapshot')->nullable()->after('criteria_responses');
        });
    }

    public function down(): void
    {
        Schema::table('jury_scores', fn (Blueprint $table) => $table->dropColumn('form_snapshot'));
    }
};
