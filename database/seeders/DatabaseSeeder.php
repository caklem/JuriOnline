<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Get roles
        $adminOwnerRole = Role::where('name', 'admin_owner')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $organizerRole = Role::where('name', 'organizer')->first();
        $participantRole = Role::where('name', 'participant')->first();
        $juryRole = Role::where('name', 'jury')->first();

        // Create demo users
        User::create([
            'name' => 'Admin Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminOwnerRole->id,
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Organizer Demo',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password'),
            'role_id' => $organizerRole->id,
        ]);

        User::create([
            'name' => 'Peserta Demo',
            'email' => 'peserta@example.com',
            'password' => Hash::make('password'),
            'role_id' => $participantRole->id,
        ]);

        User::create([
            'name' => 'Juri Demo',
            'email' => 'juri@example.com',
            'password' => Hash::make('password'),
            'role_id' => $juryRole->id,
        ]);

        // Create categories
        Category::create([
            'name' => 'Kompetisi',
            'description' => 'Event kompetisi dan perlombaan'
        ]);

        Category::create([
            'name' => 'Workshop',
            'description' => 'Event workshop dan pelatihan'
        ]);

        Category::create([
            'name' => 'Seminar',
            'description' => 'Event seminar dan konferensi'
        ]);

        Category::create([
            'name' => 'Webinar',
            'description' => 'Event webinar online'
        ]);
    }
}
