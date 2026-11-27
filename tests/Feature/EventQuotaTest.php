<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventQuotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        return User::factory()->create(array_merge(['role_id' => $role->id], $attributes));
    }

    private function makeEvent(User $organizer, string $name): Event
    {
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Event pengujian kuota.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'draft',
        ]);
    }

    private function payload(string $name = 'Event Baru'): array
    {
        return [
            'name' => $name,
            'description' => 'Deskripsi event baru.',
            'price' => 0,
            'quota' => 10,
            'registration_start' => now()->addDay()->toDateString(),
            'registration_end' => now()->addDays(5)->toDateString(),
            'event_date' => now()->addDays(10)->toDateString(),
            'event_start' => now()->addDays(10)->toDateString(),
            'event_end' => now()->addDays(11)->toDateString(),
        ];
    }

    public function test_event_admin_cannot_store_more_than_three_events(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);

        foreach (['Satu', 'Dua', 'Tiga'] as $name) {
            $this->makeEvent($admin, $name);
        }

        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/events', $this->payload('Keempat'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('quota');

        $this->assertSame(3, Event::where('organizer_id', $admin->id)->count());
    }

    public function test_event_admin_quota_is_reported_on_dashboard(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $this->makeEvent($admin, 'Satu');
        $this->makeEvent($admin, 'Dua');
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('event_quota.limit', 3)
            ->assertJsonPath('event_quota.used', 2)
            ->assertJsonPath('event_quota.remaining', 1)
            ->assertJsonPath('event_quota.can_create', true);
    }

    public function test_global_admin_is_not_limited_by_three_event_quota(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);

        foreach (['Satu', 'Dua', 'Tiga'] as $name) {
            $this->makeEvent($admin, $name);
        }

        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/events', $this->payload('Keempat'))
            ->assertCreated();

        $this->assertSame(4, Event::where('organizer_id', $admin->id)->count());
    }

    public function test_organizer_can_save_profile(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/organizer-profile', [
                'organizer_name' => 'Komunitas Seni',
                'contact_email' => 'halo@example.test',
                'phone' => '08123456789',
                'description' => 'Komunitas seni daerah.',
            ])
            ->assertOk()
            ->assertJsonPath('profile.organizer_name', 'Komunitas Seni');

        $this->assertDatabaseHas('organizer_profiles', [
            'user_id' => $admin->id,
            'organizer_name' => 'Komunitas Seni',
        ]);
    }

    public function test_admin_can_export_participants_csv(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin, 'Event Ekspor');
        $participant = $this->userWithRole(Role::PARTICIPANT);

        \App\Models\Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-EXPORT-1',
            'status' => 'verified',
            'registered_at' => now(),
        ]);

        $token = $admin->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->get("/api/admin/events/{$event->id}/participants/export");

        $response->assertOk();
        $this->assertStringContainsString('REG-EXPORT-1', $response->streamedContent());
        $this->assertStringContainsString($participant->name, $response->streamedContent());
    }
}
