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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JuryOnboardingTest extends TestCase
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

    private function makeEvent(User $organizer, string $name = 'Event Juri'): Event
    {
        $category = Category::firstOrCreate(
            ['name' => 'Kompetisi'],
            ['description' => 'Lomba']
        );

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Event untuk pengujian onboarding juri.',
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

    private function makeForm(Event $event, string $name = 'Form Juri'): JuryFormTemplate
    {
        return JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => $name,
            'criteria_fields' => [
                ['label' => 'Kesesuaian', 'type' => 'score', 'max' => 100, 'required' => true],
            ],
            'use_default_score' => true,
        ]);
    }

    public function test_panitia_can_create_jury_account_with_access_code_and_assign_it(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $response = $this->actingAs($admin)->post(route('events.jury-accounts.store', $event), [
            'name' => 'Juri Baru',
            'email' => 'juri.baru@example.com',
            'jury_form_template_id' => $form->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('jury_credentials');

        $jury = User::where('email', 'juri.baru@example.com')->firstOrFail();
        $this->assertTrue($jury->isJury());
        $this->assertTrue($jury->isActive());

        $credentials = session('jury_credentials');
        $this->assertSame('Juri Baru', $credentials['name']);
        $this->assertSame('juri.baru@example.com', $credentials['email']);
        $this->assertSame($form->name, $credentials['form']);
        $this->assertSame(8, strlen($credentials['code']));
        $this->assertTrue(Hash::check($credentials['code'], $jury->password));

        $this->assertDatabaseHas('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);

        // Demonstrate that the committee-provided code is a working login credential.
        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'juri.baru@example.com',
            'password' => $credentials['code'],
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($jury);
        $this->get('/dashboard')->assertOk()->assertSee('Dashboard Juri');
    }

    public function test_jury_role_label_is_stored_verbatim_when_creating_account(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $this->actingAs($admin)->post(route('events.jury-accounts.store', $event), [
            'name' => 'Juri Vokal',
            'email' => 'juri.vokal@example.com',
            'jury_form_template_id' => $form->id,
            'jury_role' => 'Juri Vokal',
        ])->assertSessionHas('success');

        $jury = User::where('email', 'juri.vokal@example.com')->firstOrFail();

        $this->assertDatabaseHas('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'jury_role' => 'Juri Vokal',
        ]);
    }

    public function test_panitia_can_reset_assigned_jury_password_and_new_code_logs_in(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $admin->id]);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'jury_role' => 'Juri Vokal',
        ]);

        $this->actingAs($admin)
            ->post(route('events.jury-assignments.reset-password', [$event, $assignment]))
            ->assertRedirect()
            ->assertSessionHas('jury_password_reset');

        $reset = session('jury_password_reset');
        $this->assertSame('Juri Vokal', $reset['role']);
        $this->assertSame(8, strlen($reset['code']));
        $this->assertTrue(Hash::check($reset['code'], $jury->fresh()->password));

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', [
            'email' => $jury->email,
            'password' => $reset['code'],
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($jury);
    }

    public function test_event_admin_cannot_reset_jury_password_of_another_event(): void
    {
        $adminA = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $adminB = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $eventB = $this->makeEvent($adminB, 'Event Admin B');
        $formB = $this->makeForm($eventB);
        $jury = $this->userWithRole(Role::JURY);
        $originalPassword = $jury->password;

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $eventB->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $formB->id,
        ]);

        $this->actingAs($adminA)
            ->post(route('events.jury-assignments.reset-password', [$eventB, $assignment]))
            ->assertForbidden();

        $this->assertSame($originalPassword, $jury->fresh()->password);
    }

    public function test_panitia_can_set_a_custom_jury_password(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $admin->id]);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);

        $this->actingAs($admin)
            ->post(route('events.jury-assignments.reset-password', [$event, $assignment]), [
                'password' => 'juri2026',
            ])
            ->assertRedirect()
            ->assertSessionHas('jury_password_reset');

        $reset = session('jury_password_reset');
        $this->assertTrue($reset['custom']);
        $this->assertSame('juri2026', $reset['code']);
        $this->assertTrue(Hash::check('juri2026', $jury->fresh()->password));

        $this->post('/logout');
        $this->post('/login', [
            'email' => $jury->email,
            'password' => 'juri2026',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($jury);
    }

    public function test_custom_jury_password_shorter_than_eight_characters_is_rejected(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $admin->id]);
        $originalPassword = $jury->password;

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);

        $this->actingAs($admin)
            ->post(route('events.jury-assignments.reset-password', [$event, $assignment]), [
                'password' => 'abc',
            ])
            ->assertSessionHasErrors('password')
            ->assertSessionHas('jury_password_error_assignment', $assignment->id);

        $this->assertSame($originalPassword, $jury->fresh()->password);
    }

    public function test_empty_password_falls_back_to_generated_access_code(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $admin->id]);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);

        $this->actingAs($admin)
            ->post(route('events.jury-assignments.reset-password', [$event, $assignment]), [
                'password' => '',
            ])
            ->assertSessionHas('jury_password_reset');

        $reset = session('jury_password_reset');
        $this->assertFalse($reset['custom']);
        $this->assertSame(8, strlen($reset['code']));
        $this->assertTrue(Hash::check($reset['code'], $jury->fresh()->password));
    }

    public function test_password_reset_warns_when_jury_serves_other_events(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $otherEvent = $this->makeEvent($admin, 'Event Lain');
        $form = $this->makeForm($event);
        $otherForm = $this->makeForm($otherEvent, 'Form Lain');
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $admin->id]);

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);

        EventJuryFormAssignment::create([
            'event_id' => $otherEvent->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $otherForm->id,
        ]);

        $this->actingAs($admin)
            ->post(route('events.jury-assignments.reset-password', [$event, $assignment]), [
                'password' => 'juri2026',
            ])
            ->assertSessionHas('jury_password_reset');

        $this->assertSame(['Event Lain'], session('jury_password_reset')['other_events']);
    }

    public function test_existing_jury_is_assigned_without_creating_a_second_account(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $jury = $this->userWithRole(Role::JURY);
        $juryCount = User::whereHas('role', fn ($query) => $query->where('name', Role::JURY))->count();

        $response = $this->actingAs($admin)->post(route('events.jury-accounts.store', $event), [
            'name' => $jury->name,
            'email' => $jury->email,
            'jury_form_template_id' => $form->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionMissing('jury_credentials');

        $this->assertSame(
            $juryCount,
            User::whereHas('role', fn ($query) => $query->where('name', Role::JURY))->count()
        );
        $this->assertDatabaseHas('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);
    }

    public function test_email_already_used_by_non_jury_is_rejected(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);
        $participant = $this->userWithRole(Role::PARTICIPANT);

        $response = $this->actingAs($admin)->post(route('events.jury-accounts.store', $event), [
            'name' => 'Bukan Juri',
            'email' => $participant->email,
            'jury_form_template_id' => $form->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $participant->id,
        ]);
        $this->assertTrue($participant->fresh()->isParticipant());
    }

    public function test_event_admin_cannot_create_jury_account_for_another_event(): void
    {
        $adminA = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $adminB = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $eventB = $this->makeEvent($adminB, 'Event Admin B');
        $formB = $this->makeForm($eventB);

        $this->actingAs($adminA)->post(route('events.jury-accounts.store', $eventB), [
            'name' => 'Juri Ilegal',
            'email' => 'juri.ilegal@example.com',
            'jury_form_template_id' => $formB->id,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'juri.ilegal@example.com']);
    }

    public function test_jury_account_cannot_use_form_from_another_event(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($admin);
        $otherEvent = $this->makeEvent($admin, 'Event Lain');
        $otherForm = $this->makeForm($otherEvent, 'Form Event Lain');

        $response = $this->actingAs($admin)->post(route('events.jury-accounts.store', $event), [
            'name' => 'Juri Form Salah',
            'email' => 'juri.form.salah@example.com',
            'jury_form_template_id' => $otherForm->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'juri.form.salah@example.com']);
    }

    public function test_jury_self_registration_normalizes_uppercase_email(): void
    {
        $this->post(route('register.jury.store'), [
            'name' => 'Juri Huruf Besar',
            'email' => 'JURI.HURUF.BESAR@EXAMPLE.COM',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'juri.huruf.besar@example.com']);
        $this->assertAuthenticated();
    }

    public function test_jury_can_self_register_and_sees_no_data_before_assignment(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $event = $this->makeEvent($admin);
        $form = $this->makeForm($event);

        $this->get(route('register.jury'))
            ->assertOk()
            ->assertSee('Daftar Akun Juri');

        $response = $this->post(route('register.jury.store'), [
            'name' => 'Juri Mandiri',
            'email' => 'juri.mandiri@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        $jury = User::where('email', 'juri.mandiri@example.com')->firstOrFail();
        $this->assertTrue($jury->isJury());
        $this->assertTrue($jury->isActive());
        $this->assertAuthenticatedAs($jury);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Juri')
            ->assertSee('Belum ada undangan yang Anda terima');

        $this->get(route('jury.participants', $event))->assertForbidden();

        $assignment = EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
        ]);

        // Assignment baru masih menunggu persetujuan juri.
        $this->get(route('jury.participants', $event))->assertForbidden();

        $this->post(route('jury.invitations.accept', $assignment))->assertSessionHas('success');

        $this->get(route('jury.participants', $event))->assertOk();
    }
}
