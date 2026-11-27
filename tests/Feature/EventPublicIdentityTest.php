<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPublicIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->firstOrFail()->id,
            'event_scope' => 'own',
        ]);
    }

    private function makeEvent(User $admin, array $attributes = []): Event
    {
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        return Event::create(array_merge([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Festival Inovasi Digital',
            'description' => 'Event pengujian identitas publik.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_date' => now()->addDays(2),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'active',
        ], $attributes));
    }

    public function test_slug_is_generated_and_public_event_can_be_opened_by_slug(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $this->assertSame('festival-inovasi-digital', $event->slug);

        $this->getJson('/api/public/events/by-slug/' . $event->slug)
            ->assertOk()
            ->assertJsonPath('event.id', $event->id)
            ->assertJsonPath('event.slug', $event->slug);
    }

    public function test_admin_can_store_public_identity_fields(): void
    {
        $admin = $this->admin();
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/events', [
                'name' => 'Event Hybrid Baru',
                'slug' => 'event-hybrid-baru',
                'description' => 'Deskripsi event.',
                'format' => 'hybrid',
                'timezone' => 'Asia/Makassar',
                'contact_name' => 'Panitia Satu',
                'contact_email' => 'panitia@example.test',
                'contact_phone' => '0812',
                'category_id' => $category->id,
                'price' => 0,
                'quota' => 10,
                'registration_start' => now()->addDay()->toDateString(),
                'registration_end' => now()->addDays(5)->toDateString(),
                'event_date' => now()->addDays(10)->toDateString(),
                'event_start' => now()->addDays(10)->toDateString(),
                'event_end' => now()->addDays(11)->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('event.slug', 'event-hybrid-baru')
            ->assertJsonPath('event.format', 'hybrid')
            ->assertJsonPath('event.timezone', 'Asia/Makassar');
    }

    public function test_archived_event_disappears_from_public_listing(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);

        $this->getJson('/api/public/events')->assertJsonPath('total', 1);

        $event->update(['archived_at' => now()]);

        $this->getJson('/api/public/events')->assertJsonPath('total', 0);
    }

    public function test_duplicate_event_creates_draft_copy_without_registrations(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('event.status', 'draft');

        $copy = Event::where('name', 'Festival Inovasi Digital (Salinan)')->firstOrFail();
        $this->assertSame(0, $copy->registered_count);
        $this->assertNotSame($event->slug, $copy->slug);
    }
}
