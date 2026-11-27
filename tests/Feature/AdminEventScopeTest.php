<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomEventFormTemplate;
use App\Models\CustomJuryFormTemplate;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventScopeTest extends TestCase
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

    private function makeEvent(User $organizer, string $name): Event
    {
        $category = Category::firstOrCreate(
            ['name' => 'Kompetisi'],
            ['description' => 'Lomba']
        );

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Event pengujian scope admin.',
            'price' => 1000,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'active',
            'scoring_enabled' => true,
        ]);
    }

    public function test_event_admin_can_manage_only_own_event_pages(): void
    {
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $ownEvent = $this->makeEvent($eventAdmin, 'Event Milik Admin Event');

        $this->actingAs($eventAdmin);

        foreach ([
            '/dashboard',
            '/my-events',
            "/events/{$ownEvent->id}",
            "/events/{$ownEvent->id}/edit",
            "/events/{$ownEvent->id}/form",
            "/events/{$ownEvent->id}/form/custom",
            "/events/{$ownEvent->id}/jury-form",
            "/events/{$ownEvent->id}/jury-form/custom",
            "/events/{$ownEvent->id}/jury-assignments",
            "/events/{$ownEvent->id}/participants",
            "/events/{$ownEvent->id}/recap",
            '/profile',
        ] as $path) {
            $this->get($path)->assertOk();
        }

        $this->get('/my-events')->assertSee($ownEvent->name);
        $this->get('/dashboard')->assertSee('Dashboard Admin Event');
    }

    public function test_event_admin_cannot_manage_other_events(): void
    {
        $globalAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $otherEvent = $this->makeEvent($globalAdmin, 'Event Milik Admin Lain');

        $this->actingAs($eventAdmin);

        foreach ([
            "/events/{$otherEvent->id}",
            "/events/{$otherEvent->id}/edit",
            "/events/{$otherEvent->id}/form",
            "/events/{$otherEvent->id}/jury-form",
            "/events/{$otherEvent->id}/jury-assignments",
            "/events/{$otherEvent->id}/participants",
            "/events/{$otherEvent->id}/recap",
            '/payments/all',
            '/jury/all-scores',
            '/owner/users',
        ] as $path) {
            $this->get($path)->assertForbidden();
        }

        $this->get("/events/{$otherEvent->id}")
            ->assertForbidden()
            ->assertSee('Akses ditolak')
            ->assertSee('Admin Event');

        $this->get('/my-events')->assertDontSee($otherEvent->name);
    }

    public function test_global_admin_can_manage_all_events(): void
    {
        $globalAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $eventA = $this->makeEvent($globalAdmin, 'Event Admin Global');
        $eventB = $this->makeEvent($eventAdmin, 'Event Admin Event');

        $this->actingAs($globalAdmin);

        $this->get("/events/{$eventA->id}")->assertOk();
        $this->get("/events/{$eventB->id}")->assertOk();
        $this->get("/events/{$eventA->id}/edit")->assertOk();
        $this->get("/events/{$eventB->id}/edit")->assertOk();
        $this->get('/payments/all')->assertOk();
        $this->get('/jury/all-scores')->assertOk();
        $this->get('/owner/users')->assertForbidden();
        $this->get('/my-events')
            ->assertSee($eventA->name)
            ->assertSee($eventB->name);
    }

    public function test_event_admin_cannot_access_other_event_registration_or_payment(): void
    {
        $globalAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $otherEvent = $this->makeEvent($globalAdmin, 'Event Dengan Pendaftaran');

        $registration = Registration::create([
            'event_id' => $otherEvent->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-SCOPE-0001',
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        $payment = Payment::create([
            'registration_id' => $registration->id,
            'amount' => 1000,
            'proof_file' => 'payment-proofs/scope-test.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($eventAdmin);

        $this->get("/registrations/{$registration->id}")->assertForbidden();
        $this->post("/payments/{$payment->id}/verify", ['status' => 'verified'])->assertForbidden();
        $this->get("/jury/registrations/{$registration->id}/score")->assertForbidden();
    }

    public function test_admin_cannot_score_unverified_registration(): void
    {
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $event = $this->makeEvent($eventAdmin, 'Event Peserta Belum Verifikasi');

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-SCOPE-PENDING',
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        $this->actingAs($eventAdmin);

        $this->get("/jury/registrations/{$registration->id}/score")->assertForbidden();
        $this->post("/jury/registrations/{$registration->id}/score", [
            'score' => 80,
            'public_feedback' => 'Tidak boleh.',
            'private_evaluation' => 'Tidak boleh.',
        ])->assertForbidden();
    }

    public function test_event_admin_cannot_edit_or_use_other_admins_custom_templates(): void
    {
        $globalAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $ownEvent = $this->makeEvent($eventAdmin, 'Event Template Sendiri');

        $eventTemplate = CustomEventFormTemplate::create([
            'name' => 'Template Admin Lain',
            'description' => 'Milik admin global.',
            'form_fields' => [],
            'created_by' => $globalAdmin->id,
        ]);

        $juryTemplate = CustomJuryFormTemplate::create([
            'name' => 'Template Juri Admin Lain',
            'description' => 'Milik admin global.',
            'criteria_fields' => [],
            'use_default_score' => true,
            'created_by' => $globalAdmin->id,
        ]);

        $this->actingAs($eventAdmin);

        $this->put("/custom-event-templates/{$eventTemplate->id}", [
            'name' => 'Hack',
            'description' => '',
            'form_fields' => '[]',
        ])->assertForbidden();

        $this->post("/events/{$ownEvent->id}/form/custom-template/{$eventTemplate->id}")
            ->assertForbidden();

        $this->put("/custom-jury-templates/{$juryTemplate->id}", [
            'name' => 'Hack',
            'description' => '',
            'criteria_fields' => '[]',
        ])->assertForbidden();

        $this->post("/events/{$ownEvent->id}/jury-form/custom-template/{$juryTemplate->id}")
            ->assertForbidden();
    }
}
