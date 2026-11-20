<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomJuryFormTemplate;
use App\Models\Event;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\JuryScore;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
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

    private function makeEvent(User $organizer, string $name = 'Event Uji'): Event
    {
        $category = Category::firstOrCreate(
            ['name' => 'Kompetisi'],
            ['description' => 'Lomba']
        );

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Event pengujian keamanan.',
            'price' => 1000,
            'quota' => 10,
            'registered_count' => 1,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'active',
            'scoring_enabled' => true,
            'require_official_email' => false,
        ]);
    }

    private function makeRegistration(Event $event, User $participant): Registration
    {
        return Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-' . $participant->id,
            'status' => 'pending',
            'registered_at' => now(),
        ]);
    }

    private function makeJuryForm(Event $event): JuryFormTemplate
    {
        return JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Penilaian',
            'criteria_fields' => [
                ['label' => 'Kreativitas', 'type' => 'score', 'max' => 100],
            ],
            'use_default_score' => true,
        ]);
    }

    private function makeAssignment(Event $event, User $jury, JuryFormTemplate $form, string $status = EventJuryFormAssignment::STATUS_ACCEPTED): EventJuryFormAssignment
    {
        return EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => $status,
        ]);
    }

    public function test_payment_queue_does_not_interpolate_names_into_javascript(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $participant = $this->userWithRole(Role::PARTICIPANT, [
            'name' => "x');alert(1);//",
        ]);
        $registration = $this->makeRegistration($event, $participant);
        Payment::create([
            'registration_id' => $registration->id,
            'amount' => 1000,
            'proof_file' => 'payment-proofs/proof.jpg',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/payments/pending');

        $response->assertOk();
        // Payload stays inert inside the data attribute, never inside a JS call.
        $response->assertSee('data-payment-name="x&#039;);alert(1);//"', false);
        $this->assertStringNotContainsString(
            "showVerifyModal(1, 'x');alert(1);//",
            $response->getContent()
        );
    }

    public function test_event_admin_cannot_reset_password_of_jury_owned_by_another_admin(): void
    {
        $otherAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($eventAdmin);
        $form = $this->makeJuryForm($event);
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $otherAdmin->id]);
        $assignment = $this->makeAssignment($event, $jury, $form);
        $originalPassword = $jury->password;

        $this->actingAs($eventAdmin)
            ->post("/events/{$event->id}/jury-assignments/{$assignment->id}/reset-password", [
                'password' => 'NewPassword123!',
            ])
            ->assertForbidden();

        $this->assertSame($originalPassword, $jury->fresh()->password);
    }

    public function test_event_admin_can_reset_password_of_jury_they_created(): void
    {
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($eventAdmin);
        $form = $this->makeJuryForm($event);
        $jury = $this->userWithRole(Role::JURY, ['created_by' => $eventAdmin->id]);
        $assignment = $this->makeAssignment($event, $jury, $form);

        $this->actingAs($eventAdmin)
            ->post("/events/{$event->id}/jury-assignments/{$assignment->id}/reset-password", [
                'password' => 'NewPassword123!',
            ])
            ->assertRedirect();

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('NewPassword123!', $jury->fresh()->password)
        );
    }

    public function test_attaching_another_admins_jury_only_sends_an_invitation_and_cannot_touch_credentials(): void
    {
        $otherAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($eventAdmin);
        $form = $this->makeJuryForm($event);
        $foreignJury = $this->userWithRole(Role::JURY, ['created_by' => $otherAdmin->id]);
        $originalPassword = $foreignJury->password;

        // Attaching someone else's jury is a consent-based invitation, not a takeover.
        $this->actingAs($eventAdmin)
            ->post("/events/{$event->id}/jury-accounts", [
                'name' => $foreignJury->name,
                'email' => $foreignJury->email,
                'jury_form_template_id' => $form->id,
            ])
            ->assertSessionHas('success');

        $assignment = EventJuryFormAssignment::where('event_id', $event->id)
            ->where('jury_id', $foreignJury->id)
            ->firstOrFail();

        $this->assertSame(EventJuryFormAssignment::STATUS_PENDING, $assignment->status);
        $this->assertSame($originalPassword, $foreignJury->fresh()->password);

        // Credential takeover of that account stays impossible for a scoped admin.
        $this->actingAs($eventAdmin)
            ->post("/events/{$event->id}/jury-assignments/{$assignment->id}/reset-password", [
                'password' => 'HijackedPassword123!',
            ])
            ->assertForbidden();

        $this->assertSame($originalPassword, $foreignJury->fresh()->password);
    }

    public function test_jury_index_hides_accounts_from_other_organizers(): void
    {
        $otherAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($eventAdmin);
        $this->makeJuryForm($event);
        $foreignJury = $this->userWithRole(Role::JURY, [
            'created_by' => $otherAdmin->id,
            'name' => 'Juri Milik Orang Lain',
        ]);

        $this->actingAs($eventAdmin)
            ->get("/events/{$event->id}/jury-assignments")
            ->assertOk()
            ->assertDontSee($foreignJury->email);
    }

    public function test_save_as_template_requires_managing_the_event(): void
    {
        $otherAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $eventAdmin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $event = $this->makeEvent($otherAdmin);

        $this->actingAs($eventAdmin)
            ->post("/events/{$event->id}/jury-form/save-template", [
                'template_name' => 'Template Bajakan',
                'criteria_fields' => json_encode([
                    ['label' => 'Kreativitas', 'type' => 'score', 'max' => 100],
                ]),
            ])
            ->assertForbidden();

        $this->assertSame(0, CustomJuryFormTemplate::where('name', 'Template Bajakan')->count());
    }

    public function test_payment_proof_is_served_only_to_owner_or_event_manager(): void
    {
        Storage::fake('local');

        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $owner = $this->userWithRole(Role::PARTICIPANT);
        $stranger = $this->userWithRole(Role::PARTICIPANT);
        $registration = $this->makeRegistration($event, $owner);

        Storage::disk('local')->put('payment-proofs/bukti.jpg', 'proof-bytes');

        $payment = Payment::create([
            'registration_id' => $registration->id,
            'amount' => 1000,
            'proof_file' => 'payment-proofs/bukti.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->get("/payments/{$payment->id}/proof")
            ->assertOk();

        $this->actingAs($stranger)
            ->get("/payments/{$payment->id}/proof")
            ->assertForbidden();

        $this->actingAs($admin)
            ->get("/payments/{$payment->id}/proof")
            ->assertOk();

        $this->app['auth']->logout();

        $this->get("/payments/{$payment->id}/proof")
            ->assertRedirect('/login');
    }

    public function test_uploaded_proof_is_stored_on_the_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $event = $this->makeEvent($this->userWithRole(Role::ADMIN, ['event_scope' => 'all']));
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $registration = $this->makeRegistration($event, $participant);

        $this->actingAs($participant)
            ->post("/registrations/{$registration->id}/payment", [
                'proof_file' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();

        $payment = $registration->fresh()->payment;
        $this->assertNotNull($payment);
        Storage::disk('local')->assertExists($payment->proof_file);
        Storage::disk('public')->assertMissing($payment->proof_file);
    }

    public function test_jury_cannot_update_score_after_assignment_is_revoked(): void
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'all']);
        $event = $this->makeEvent($admin);
        $form = $this->makeJuryForm($event);
        $jury = $this->userWithRole(Role::JURY);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $registration = $this->makeRegistration($event, $participant);
        $registration->update(['status' => 'verified']);

        $assignment = $this->makeAssignment($event, $jury, $form);

        $score = JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'score' => 80,
            'public_feedback' => 'Bagus',
            'private_evaluation' => 'Catatan',
        ]);

        $this->actingAs($jury)
            ->patch("/jury/scores/{$score->id}", ['score' => 90])
            ->assertRedirect();

        $this->assertSame('90.00', $score->fresh()->score);

        $assignment->update(['status' => EventJuryFormAssignment::STATUS_DECLINED]);

        $this->actingAs($jury)
            ->patch("/jury/scores/{$score->id}", ['score' => 10])
            ->assertForbidden();

        $this->assertSame('90.00', $score->fresh()->score);
    }

    public function test_browser_hardening_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString(
            "default-src 'self'",
            $response->headers->get('Content-Security-Policy')
        );
        $response->assertHeaderMissing('X-Powered-By');
    }
}
