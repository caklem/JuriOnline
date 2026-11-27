<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function scenario(): array
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->firstOrFail()->id,
            'event_scope' => 'own',
        ]);
        $participant = User::factory()->create([
            'role_id' => Role::where('name', Role::PARTICIPANT)->firstOrFail()->id,
        ]);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Tim',
            'description' => 'Event tim.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_date' => now()->addDay(),
            'event_start' => now()->addDay(),
            'event_end' => now()->addDays(2),
            'status' => 'active',
            'participation_mode' => 'team',
            'team_min_members' => 2,
            'team_max_members' => 3,
        ]);

        return [$participant, $event];
    }

    public function test_team_event_requires_team_name_and_members(): void
    {
        [$participant, $event] = $this->scenario();
        $token = $participant->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/events/{$event->id}/register", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['team_name', 'team_members']);
    }

    public function test_team_registration_is_saved_with_members(): void
    {
        [$participant, $event] = $this->scenario();
        $token = $participant->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/events/{$event->id}/register", [
                'team_name' => 'Tim Melodi',
                'team_members' => [
                    ['name' => 'Ani', 'email' => 'ani@example.test'],
                    ['name' => 'Budi'],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'team_name' => 'Tim Melodi',
        ]);

        $registration = \App\Models\Registration::firstOrFail();
        $this->assertCount(2, $registration->team_members);
    }
}
