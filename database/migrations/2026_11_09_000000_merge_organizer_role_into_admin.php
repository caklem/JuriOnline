<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        $organizerId = DB::table('roles')->where('name', 'organizer')->value('id');

        if ($adminId && $organizerId) {
            DB::table('users')
                ->where('role_id', $organizerId)
                ->update(['role_id' => $adminId]);
        }

        DB::table('users')
            ->where('email', 'organizer@example.com')
            ->update(['name' => 'Admin Event']);

        if ($organizerId) {
            DB::table('roles')->where('id', $organizerId)->delete();
        }
    }

    public function down(): void
    {
        if (!DB::table('roles')->where('name', 'organizer')->exists()) {
            DB::table('roles')->insert([
                'name' => 'organizer',
                'display_name' => 'Organizer',
                'description' => 'Penyelenggara event yang dapat membuat dan mengelola event',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
