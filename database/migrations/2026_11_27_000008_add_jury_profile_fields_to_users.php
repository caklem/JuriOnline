<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('organization')->nullable()->after('phone');
            $table->string('expertise')->nullable()->after('organization');
            $table->text('bio')->nullable()->after('expertise');
            $table->string('whatsapp_number', 40)->nullable()->after('bio');
            $table->boolean('whatsapp_consent')->default(false)->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'organization', 'expertise', 'bio', 'whatsapp_number', 'whatsapp_consent']);
        });
    }
};
