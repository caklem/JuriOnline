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

class JuryScoringViewTest extends TestCase
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

    private function makeEvent(User $organizer): Event
    {
        $category = Category::firstOrCreate(
            ['name' => 'Kompetisi'],
            ['description' => 'Lomba']
        );

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Event Penilaian',
            'description' => 'Event untuk pengujian tampilan juri.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 1,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'active',
            'scoring_enabled' => true,
        ]);
    }

    private function makeForm(Event $event): JuryFormTemplate
    {
        return JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Juri',
            'criteria_fields' => [
                ['label' => 'Keserasian Irama', 'type' => 'score', 'required' => true, 'max' => 5],
                ['label' => 'Tempo', 'type' => 'score', 'required' => true, 'max' => 5],
            ],
            'use_default_score' => true,
        ]);
    }

    private function makeAcceptedAssignment(Event $event, User $jury, JuryFormTemplate $form): EventJuryFormAssignment
    {
        return EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
    }

    private function makeVerifiedRegistration(Event $event, User $participant): Registration
    {
        return Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-' . $participant->id,
            'status' => 'verified',
            'registered_at' => now(),
        ]);
    }

    public function test_participants_page_shows_own_score_separately_from_the_event_average(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $registration = $this->makeVerifiedRegistration($event, $participant);

        $me = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $me, $form);

        $otherJury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $otherJury, $form);

        JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $me->id,
            'jury_form_template_id' => $form->id,
            'score' => 40,
        ]);
        JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $otherJury->id,
            'jury_form_template_id' => $form->id,
            'score' => 60,
        ]);

        $response = $this->actingAs($me)->get("/jury/events/{$event->id}/participants");

        $response->assertOk();
        $response->assertSee('Rata-rata: 50.00');
        $response->assertSee('2 juri menilai');
        $response->assertSee('Nilai Anda', false);
        $response->assertSee('40.00');
        $response->assertSee('Edit Penilaian');
        // The other jury's score is visible, but the edit link stays on my own row.
        $myScore = JuryScore::where('jury_id', $me->id)->firstOrFail();
        $response->assertSee(route('jury.edit-score', $myScore));
    }

    public function test_required_criteria_are_enforced_server_side_with_indonesian_messages(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $registration = $this->makeVerifiedRegistration($event, $participant);

        $jury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $jury, $form);

        $response = $this->actingAs($jury)->post("/jury/registrations/{$registration->id}/score", [
            'score' => 0,
            'public_feedback' => 'tanpa nilai kriteria',
        ]);

        $response->assertSessionHasErrors(['criteria_0', 'criteria_1']);
        $errors = session('errors');
        $this->assertSame('Keserasian Irama wajib diisi.', $errors->first('criteria_0'));
        $this->assertSame('Tempo wajib diisi.', $errors->first('criteria_1'));
        $this->assertDatabaseCount('jury_scores', 0);
    }

    public function test_accepting_an_invitation_redirects_to_the_participants_page(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);
        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_PENDING,
        ]);

        $this->actingAs($jury)
            ->post(route('jury.invitations.accept', $assignment))
            ->assertRedirect(route('jury.participants', $event))
            ->assertSessionHas('success');

        $this->assertSame(EventJuryFormAssignment::STATUS_ACCEPTED, $assignment->fresh()->status);
    }

    public function test_dashboard_lists_participants_waiting_for_my_score(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $registration = $this->makeVerifiedRegistration($event, $participant);

        $jury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $jury, $form);

        $this->actingAs($jury)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Perlu Anda Nilai')
            ->assertSee($participant->name)
            ->assertSee(route('jury.score', $registration), false);

        JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'score' => 80,
        ]);

        $this->actingAs($jury)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Tidak ada yang menunggu');
    }

    public function test_saving_a_score_moves_the_jury_to_the_next_unscored_participant(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $first = $this->userWithRole(Role::PARTICIPANT, ['name' => 'Peserta Satu']);
        $second = $this->userWithRole(Role::PARTICIPANT, ['name' => 'Peserta Dua']);
        $firstRegistration = $this->makeVerifiedRegistration($event, $first);
        $secondRegistration = $this->makeVerifiedRegistration($event, $second);

        $jury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $jury, $form);

        $payload = [
            'score' => 0,
            'criteria_0' => 4,
            'criteria_1' => 5,
            'public_feedback' => 'Bagus',
        ];

        $this->actingAs($jury)
            ->post("/jury/registrations/{$firstRegistration->id}/score", $payload)
            ->assertRedirect(route('jury.score', $secondRegistration))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('jury_scores', [
            'registration_id' => $firstRegistration->id,
            'jury_id' => $jury->id,
            'score' => 9,
        ]);

        $this->actingAs($jury)
            ->post("/jury/registrations/{$secondRegistration->id}/score", $payload)
            ->assertRedirect(route('jury.participants', $event))
            ->assertSessionHas('success');
    }

    public function test_participants_page_shows_scoring_progress(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $jury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $jury, $form);

        $scored = $this->makeVerifiedRegistration($event, $this->userWithRole(Role::PARTICIPANT));
        $this->makeVerifiedRegistration($event, $this->userWithRole(Role::PARTICIPANT));

        JuryScore::create([
            'registration_id' => $scored->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'score' => 70,
        ]);

        $this->actingAs($jury)
            ->get("/jury/events/{$event->id}/participants")
            ->assertOk()
            ->assertSee('Progres penilaian Anda')
            ->assertSee('50%');
    }

    public function test_jury_can_open_the_assigned_form_preview(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $jury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $jury, $form);

        $this->actingAs($jury)
            ->get("/jury/events/{$event->id}/form")
            ->assertOk()
            ->assertSee($form->name)
            ->assertSee('Keserasian Irama')
            ->assertSee('Tempo')
            ->assertSee('10 poin');
    }

    public function test_jury_cannot_open_a_form_for_an_event_they_never_accepted(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $this->makeForm($event);

        $jury = $this->userWithRole(Role::JURY);

        $this->actingAs($jury)
            ->get("/jury/events/{$event->id}/form")
            ->assertForbidden();
    }

    public function test_event_card_shows_the_event_day_instead_of_the_registration_period(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $jury = $this->userWithRole(Role::JURY);
        $this->makeAcceptedAssignment($event, $jury, $form);

        $response = $this->actingAs($jury)->get('/jury/events');

        $response->assertOk();
        $response->assertSee('Hari Pelaksanaan');
        $response->assertSee($event->event_start->format('d M Y'));
        $response->assertDontSee($event->registration_start->format('d M Y'));
    }
}
