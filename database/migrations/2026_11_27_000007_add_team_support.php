<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('participation_mode', 20)->default('individual')->after('format');
            $table->unsignedTinyInteger('team_min_members')->default(2)->after('participation_mode');
            $table->unsignedTinyInteger('team_max_members')->default(5)->after('team_min_members');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('team_name')->nullable()->after('registration_number');
            $table->json('team_members')->nullable()->after('team_name');
        });
    }

    public function down(): void
    {
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn(['participation_mode', 'team_min_members', 'team_max_members']));
        Schema::table('registrations', fn (Blueprint $table) => $table->dropColumn(['team_name', 'team_members']));
    }
};
