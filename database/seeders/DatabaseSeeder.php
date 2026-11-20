<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $adminOwnerRole = Role::where('name', 'admin_owner')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $participantRole = Role::where('name', 'participant')->firstOrFail();
        $juryRole = Role::where('name', 'jury')->firstOrFail();

        // Create or update demo users
        $demoUsers = [
            [
                'name' => 'Admin Owner',
                'email' => 'owner@example.com',
                'role_id' => $adminOwnerRole->id,
                'event_scope' => 'all',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'role_id' => $adminRole->id,
                'event_scope' => 'all',
            ],
            [
                'name' => 'Admin Event',
                'email' => 'organizer@example.com',
                'role_id' => $adminRole->id,
                'event_scope' => 'own',
            ],
            [
                'name' => 'Peserta Demo',
                'email' => 'peserta@example.com',
                'role_id' => $participantRole->id,
                'event_scope' => 'all',
            ],
            [
                'name' => 'Juri Demo',
                'email' => 'juri@example.com',
                'role_id' => $juryRole->id,
                'event_scope' => 'all',
            ],
        ];

        // Create or update categories
        $categories = [
            ['name' => 'Kompetisi', 'description' => 'Event kompetisi dan perlombaan'],
            ['name' => 'Workshop', 'description' => 'Event workshop dan pelatihan'],
            ['name' => 'Seminar', 'description' => 'Event seminar dan konferensi'],
            ['name' => 'Webinar', 'description' => 'Event webinar online'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        // Demo accounts with a well-known password must never exist outside a
        // developer machine: they hand out admin_owner access.
        if (!app()->environment('local', 'testing')) {
            $this->command?->warn('Demo users skipped: demo seeding is limited to local/testing environments.');

            return;
        }

        $demoPassword = $this->demoPassword();

        foreach ($demoUsers as $demoUser) {
            User::updateOrCreate(
                ['email' => $demoUser['email']],
                $demoUser + ['password' => Hash::make($demoPassword), 'is_active' => true]
            );
        }

        $this->command?->info("Demo accounts use password: {$demoPassword}");
        $this->command?->line('Override with DEMO_PASSWORD in .env; one shared password per seed run.');
    }

    /**
     * Random password per seed run so demo credentials are never guessable.
     * Set DEMO_PASSWORD in .env to pin a value for local convenience.
     */
    private function demoPassword(): string
    {
        $configured = trim((string) env('DEMO_PASSWORD', ''));

        return $configured !== '' ? $configured : Str::random(20);
    }
}
