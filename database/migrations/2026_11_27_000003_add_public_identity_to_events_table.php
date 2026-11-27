<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('format', 20)->default('offline')->after('description');
            $table->string('timezone', 64)->default('Asia/Jakarta')->after('format');
            $table->string('contact_name')->nullable()->after('location');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Event::query()->orderBy('id')->each(function (Event $event) {
            $base = Str::slug($event->name) ?: 'event';
            $slug = $base;
            $suffix = 1;

            while (DB::table('events')->where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $base . '-' . (++$suffix);
            }

            DB::table('events')->where('id', $event->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'format', 'timezone', 'contact_name', 'contact_email', 'contact_phone', 'archived_at']);
        });
    }
};
