<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventDayTest extends TestCase
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

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
        ], $attributes));
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Event Hari H',
            'description' => 'Event untuk pengujian tanggal hari H.',
            'price' => 1000,
            'quota' => 10,
            'registration_start' => '2026-11-01',
            'registration_end' => '2026-11-20',
            'event_start' => '2026-12-01',
            'event_end' => '2026-12-05',
            'event_date' => '2026-12-02',
        ], $overrides);
    }

    public function test_admin_can_create_event_with_event_day(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);

        $this->actingAs($admin)
            ->post('/events', $this->eventPayload())
            ->assertRedirect();

        $event = Event::where('name', 'Event Hari H')->firstOrFail();
        $this->assertSame('2026-12-02', $event->event_date->format('Y-m-d'));
    }

    public function test_event_day_is_required(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $payload = $this->eventPayload();
        unset($payload['event_date']);

        $this->actingAs($admin)
            ->post('/events', $payload)
            ->assertSessionHasErrors(['event_date' => 'Tanggal hari H wajib diisi.']);

        $this->assertDatabaseMissing('events', ['name' => 'Event Hari H']);
    }

    public function test_single_day_event_is_allowed(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);

        $this->actingAs($admin)
            ->post('/events', $this->eventPayload([
                'name' => 'Event Sehari',
                'event_start' => '2026-12-02',
                'event_end' => '2026-12-02',
                'event_date' => '2026-12-02',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $event = Event::where('name', 'Event Sehari')->firstOrFail();
        $this->assertSame('2026-12-02', $event->event_date->format('Y-m-d'));
        $this->assertSame('2026-12-02', $event->event_end->format('Y-m-d'));
    }

    public function test_event_day_must_fall_inside_the_event_range(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);

        $this->actingAs($admin)
            ->post('/events', $this->eventPayload(['event_date' => '2026-11-15']))
            ->assertSessionHasErrors(['event_date' => 'Tanggal hari H tidak boleh sebelum tanggal mulai event.']);
    }

    public function test_edit_form_prefills_the_event_day(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Edit Hari H',
            'description' => 'Deskripsi',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => '2026-11-01',
            'registration_end' => '2026-11-20',
            'event_date' => '2026-12-02',
            'event_start' => '2026-12-01',
            'event_end' => '2026-12-05',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get("/events/{$event->id}/edit")
            ->assertOk()
            ->assertSee('Tanggal Hari H')
            ->assertSee('value="2026-12-02"', false);

        // Update menyimpan hari H baru.
        $this->actingAs($admin)
            ->put("/events/{$event->id}", $this->eventPayload([
                'name' => 'Event Edit Hari H',
                'status' => 'draft',
                'event_date' => '2026-12-03',
            ]))
            ->assertRedirect();

        $this->assertSame('2026-12-03', $event->fresh()->event_date->format('Y-m-d'));
    }

    public function test_jury_card_shows_only_the_event_day(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Multi Hari',
            'description' => 'Deskripsi',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => '2026-11-01',
            'registration_end' => '2026-11-20',
            'event_date' => '2026-12-02',
            'event_start' => '2026-12-01',
            'event_end' => '2026-12-05',
            'status' => 'active',
        ]);

        $form = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Hari H',
            'criteria_fields' => [['label' => 'Kreativitas', 'type' => 'score', 'required' => true, 'max' => 10]],
            'use_default_score' => true,
        ]);

        $jury = $this->userWithRole(Role::JURY);
        EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        $this->actingAs($jury)
            ->get('/jury/events')
            ->assertOk()
            ->assertSee('Hari Pelaksanaan')
            ->assertSee('02 Dec 2026')
            ->assertDontSee('05 Dec 2026');
    }
}
