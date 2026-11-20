<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\JuryScore;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringWindowTest extends TestCase
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

    private function makeEvent(User $organizer, array $overrides = []): Event
    {
        $category = Category::firstOrCreate(
            ['name' => 'Kompetisi'],
            ['description' => 'Lomba']
        );

        return Event::create(array_merge([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Event Penilaian',
            'description' => 'Event untuk pengujian jendela penilaian.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 1,
            'registration_start' => now()->subWeek(),
            'registration_end' => now()->addWeek(),
            'event_date' => now()->addDay()->toDateString(),
            'event_start' => now()->addDay()->toDateString(),
            'event_end' => now()->addDay()->toDateString(),
            'status' => 'active',
        ], $overrides));
    }

    private function makeForm(Event $event): JuryFormTemplate
    {
        return JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Jendela',
            'criteria_fields' => [
                ['label' => 'Kreativitas', 'type' => 'score', 'required' => true, 'max' => 10],
            ],
            'use_default_score' => true,
        ]);
    }

    private function makeJury(Event $event, JuryFormTemplate $form): User
    {
        $jury = $this->userWithRole(Role::JURY);

        EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        return $jury;
    }

    private function makeRegistration(Event $event): Registration
    {
        $participant = $this->userWithRole(Role::PARTICIPANT);

        return Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-' . $participant->id,
            'status' => 'verified',
            'registered_at' => now(),
        ]);
    }

    public function test_auto_mode_opens_scoring_on_the_event_day_and_closes_before_it(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);

        $future = $this->makeEvent($admin, [
            'event_date' => now()->addDay()->toDateString(),
            'event_start' => now()->addDay()->toDateString(),
            'event_end' => now()->addDay()->toDateString(),
        ]);
        $today = $this->makeEvent($admin, [
            'name' => 'Event Hari Ini',
            'event_date' => now()->toDateString(),
            'event_start' => now()->toDateString(),
            'event_end' => now()->toDateString(),
        ]);

        $this->assertSame('auto', $future->scoringMode());
        $this->assertFalse($future->isScoringOpen());
        $this->assertTrue($today->isScoringOpen());
    }

    public function test_manual_switch_overrides_the_event_day(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);

        $early = $this->makeEvent($admin, [
            'event_date' => now()->addWeek()->toDateString(),
            'event_start' => now()->addWeek()->toDateString(),
            'event_end' => now()->addWeek()->toDateString(),
            'scoring_enabled' => true,
        ]);
        $closed = $this->makeEvent($admin, [
            'name' => 'Event Ditutup',
            'event_date' => now()->toDateString(),
            'event_start' => now()->toDateString(),
            'event_end' => now()->toDateString(),
            'scoring_enabled' => false,
        ]);

        $this->assertTrue($early->isScoringOpen());
        $this->assertFalse($closed->isScoringOpen());
        $this->assertSame('Ditutup oleh panitia', $closed->scoringStatusLabel());
    }

    public function test_event_admin_can_open_close_and_reset_scoring_mode(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin, [
            'event_date' => now()->addWeek()->toDateString(),
            'event_start' => now()->addWeek()->toDateString(),
            'event_end' => now()->addWeek()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post("/events/{$event->id}/scoring", ['mode' => 'open'])
            ->assertSessionHas('success');
        $this->assertTrue($event->fresh()->scoring_enabled);

        $this->actingAs($admin)
            ->post("/events/{$event->id}/scoring", ['mode' => 'closed'])
            ->assertSessionHas('success');
        $this->assertFalse($event->fresh()->scoring_enabled);

        $this->actingAs($admin)
            ->post("/events/{$event->id}/scoring", ['mode' => 'auto'])
            ->assertSessionHas('success');
        $this->assertNull($event->fresh()->scoring_enabled);
    }

    public function test_event_admin_cannot_change_scoring_of_another_admins_event(): void
    {
        $otherAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $scopedAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($otherAdmin);

        $this->actingAs($scopedAdmin)
            ->post("/events/{$event->id}/scoring", ['mode' => 'open'])
            ->assertForbidden();

        $this->assertNull($event->fresh()->scoring_enabled);
    }

    public function test_jury_cannot_score_while_scoring_is_closed(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin, ['scoring_enabled' => false]);
        $form = $this->makeForm($event);
        $jury = $this->makeJury($event, $form);
        $registration = $this->makeRegistration($event);

        $this->actingAs($jury)
            ->post("/jury/registrations/{$registration->id}/score", [
                'score' => 0,
                'criteria_0' => 8,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('jury_scores', 0);

        $this->actingAs($jury)
            ->get("/jury/registrations/{$registration->id}/score")
            ->assertRedirect("/jury/events/{$event->id}/participants");

        $this->actingAs($jury)
            ->get("/jury/events/{$event->id}/participants")
            ->assertOk()
            ->assertSee('Penilaian belum dibuka')
            ->assertDontSee('Berikan Penilaian');
    }

    public function test_jury_can_score_once_the_admin_opens_scoring_early(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin, [
            'event_date' => now()->addWeek()->toDateString(),
            'event_start' => now()->addWeek()->toDateString(),
            'event_end' => now()->addWeek()->toDateString(),
        ]);
        $form = $this->makeForm($event);
        $jury = $this->makeJury($event, $form);
        $registration = $this->makeRegistration($event);

        $this->actingAs($admin)->post("/events/{$event->id}/scoring", ['mode' => 'open']);

        $this->actingAs($jury)
            ->post("/jury/registrations/{$registration->id}/score", [
                'score' => 0,
                'criteria_0' => 8,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('jury_scores', [
            'registration_id' => $registration->id,
            'jury_id' => $jury->id,
            'score' => 8,
        ]);
    }

    public function test_dashboard_waits_for_the_event_day_and_hides_pending_tasks(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->makeJury($event, $form);
        $this->makeRegistration($event);

        $this->actingAs($jury)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Penilaian belum dibuka')
            ->assertSee('Menunggu hari H')
            ->assertDontSee('Perlu Anda Nilai');
    }

    public function test_jury_cannot_edit_a_score_while_scoring_is_closed(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->makeJury($event, $form);
        $registration = $this->makeRegistration($event);

        $score = JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'score' => 70,
        ]);

        $event->update(['scoring_enabled' => false]);

        $this->actingAs($jury)
            ->patch("/jury/scores/{$score->id}", ['score' => 90])
            ->assertForbidden();

        $this->assertSame('70.00', $score->fresh()->score);
    }
}
