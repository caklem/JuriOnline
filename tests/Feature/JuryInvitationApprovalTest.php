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

class JuryInvitationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $roleName, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::where('name', $roleName)->firstOrFail()->id,
            'is_active' => true,
        ], $attributes));
    }

    private function makeEvent(User $organizer, string $name = 'Event Juri'): Event
    {
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Event untuk pengujian undangan juri.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'active',
        ]);
    }

    private function makeForm(Event $event): JuryFormTemplate
    {
        return JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Juri',
            'criteria_fields' => [
                ['label' => 'Kesesuaian', 'type' => 'score', 'max' => 100, 'required' => true],
            ],
            'use_default_score' => true,
        ]);
    }

    public function test_new_assignment_starts_pending_and_notifies_the_jury(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        $this->actingAs($admin)->post(route('events.jury-assignments.store', $event), [
            'jury_ids' => [$jury->id],
            'jury_form_template_id' => $form->id,
            'jury_role' => 'Juri Vokal',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'status' => EventJuryFormAssignment::STATUS_PENDING,
            'jury_role' => 'Juri Vokal',
        ]);

        $notification = $jury->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('Undangan menjadi juri', $notification->data['title']);
        $this->assertSame($event->name, $notification->data['event_name']);
        $this->assertSame('Juri Vokal', $notification->data['jury_role']);
    }

    public function test_pending_jury_cannot_access_participants_until_accepting(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_PENDING,
        ]);

        $this->actingAs($jury)->get(route('jury.participants', $event))->assertForbidden();
        $this->actingAs($jury)->get(route('jury.events'))->assertOk()->assertDontSee($event->name);

        $this->actingAs($jury)
            ->post(route('jury.invitations.accept', $assignment))
            ->assertSessionHas('success');

        $this->assertSame(EventJuryFormAssignment::STATUS_ACCEPTED, $assignment->fresh()->status);
        $this->assertNotNull($assignment->fresh()->responded_at);

        $this->actingAs($jury)->get(route('jury.participants', $event))->assertOk();
        $this->actingAs($jury)->get(route('jury.leaderboard', $event))->assertOk();
    }

    public function test_jury_can_decline_and_stays_locked_out(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
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
            ->post(route('jury.invitations.decline', $assignment))
            ->assertSessionHas('success');

        $this->assertSame(EventJuryFormAssignment::STATUS_DECLINED, $assignment->fresh()->status);
        $this->actingAs($jury)->get(route('jury.participants', $event))->assertForbidden();
    }

    public function test_jury_cannot_respond_to_another_jurys_invitation(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $owner = $this->userWithRole(Role::JURY);
        $intruder = $this->userWithRole(Role::JURY);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $owner->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_PENDING,
        ]);

        $this->actingAs($intruder)
            ->post(route('jury.invitations.accept', $assignment))
            ->assertForbidden();

        $this->assertSame(EventJuryFormAssignment::STATUS_PENDING, $assignment->fresh()->status);
    }

    public function test_responding_twice_is_rejected(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        $this->actingAs($jury)
            ->post(route('jury.invitations.decline', $assignment))
            ->assertSessionHas('error');

        $this->assertSame(EventJuryFormAssignment::STATUS_ACCEPTED, $assignment->fresh()->status);
    }

    public function test_accepting_notifies_the_organizer_and_clears_the_invitation_notification(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        $this->actingAs($admin)->post(route('events.jury-assignments.store', $event), [
            'jury_ids' => [$jury->id],
            'jury_form_template_id' => $form->id,
        ]);

        $assignment = EventJuryFormAssignment::where('jury_id', $jury->id)->firstOrFail();
        $this->assertSame(1, $jury->unreadNotifications()->count());

        $this->actingAs($jury)->post(route('jury.invitations.accept', $assignment));

        $this->assertSame(0, $jury->fresh()->unreadNotifications()->count());

        $organizerNotification = $admin->notifications()->first();
        $this->assertNotNull($organizerNotification);
        $this->assertSame('Juri menerima undangan', $organizerNotification->data['title']);
    }

    public function test_re_assigning_a_declined_jury_reopens_the_invitation(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('events.jury-assignments.store', $event), [
            'jury_ids' => [$jury->id],
            'jury_form_template_id' => $form->id,
        ]);

        $assignment = EventJuryFormAssignment::where('jury_id', $jury->id)->firstOrFail();
        $this->assertSame(EventJuryFormAssignment::STATUS_PENDING, $assignment->status);
        $this->assertNull($assignment->responded_at);
        $this->assertSame(1, $jury->notifications()->count());
    }

    public function test_existing_accepted_assignment_stays_accepted_when_role_is_updated(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => EventJuryFormAssignment::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('events.jury-assignments.store', $event), [
            'jury_ids' => [$jury->id],
            'jury_form_template_id' => $form->id,
            'jury_role' => 'Juri Adab',
        ]);

        $assignment = EventJuryFormAssignment::where('jury_id', $jury->id)->firstOrFail();
        $this->assertSame(EventJuryFormAssignment::STATUS_ACCEPTED, $assignment->status);
        $this->assertSame('Juri Adab', $assignment->jury_role);
        $this->assertSame(0, $jury->notifications()->count());
    }

    public function test_jury_can_mark_notifications_as_read(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);

        $this->actingAs($admin)->post(route('events.jury-assignments.store', $event), [
            'jury_ids' => [$jury->id],
            'jury_form_template_id' => $form->id,
        ]);

        $this->assertSame(1, $jury->unreadNotifications()->count());

        $this->actingAs($jury)
            ->post(route('jury.notifications.read'))
            ->assertRedirect();

        $this->assertSame(0, $jury->fresh()->unreadNotifications()->count());
    }
}
