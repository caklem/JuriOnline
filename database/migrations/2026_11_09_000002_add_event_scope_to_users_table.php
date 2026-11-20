<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('event_scope', 20)->default('all')->after('role_id');
        });

        DB::table('users')
            ->where('email', 'organizer@example.com')
            ->update(['event_scope' => 'own']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('event_scope');
        });
    }
};
