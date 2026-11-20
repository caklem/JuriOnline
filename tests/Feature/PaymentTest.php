<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function makeEvent(User $organizer): Event
    {
        $category = Category::create(['name' => 'Kompetisi', 'description' => 'Lomba']);

        return Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Event Pembayaran',
            'description' => 'Event untuk pengujian pembayaran.',
            'price' => 1000,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_start' => now()->addDays(2),
            'event_end' => now()->addDays(3),
            'status' => 'active',
        ]);
    }

    private function makeRegistration(Event $event, User $participant): Registration
    {
        return Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-PAY-' . $participant->id,
            'status' => 'pending',
            'registered_at' => now(),
        ]);
    }

    public function test_participant_can_upload_pdf_payment_proof(): void
    {
        Storage::fake('local');

        $admin = $this->userWithRole(Role::ADMIN);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $event = $this->makeEvent($admin);
        $registration = $this->makeRegistration($event, $participant);

        $response = $this->actingAs($participant)->post(
            route('payments.store', $registration),
            ['proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')]
        );

        $response->assertRedirect(route('registrations.show', $registration));
        $response->assertSessionHas('success');

        $payment = Payment::firstOrFail();
        $this->assertSame($registration->id, $payment->registration_id);
        $this->assertSame(1000, (int) $payment->amount);
        $this->assertSame('pending', $payment->status);
        Storage::disk('local')->assertExists($payment->proof_file);
    }

    public function test_participant_cannot_upload_payment_for_another_user(): void
    {
        Storage::fake('public');

        $admin = $this->userWithRole(Role::ADMIN);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $otherParticipant = $this->userWithRole(Role::PARTICIPANT);
        $event = $this->makeEvent($admin);
        $registration = $this->makeRegistration($event, $otherParticipant);

        $this->actingAs($participant)->post(
            route('payments.store', $registration),
            ['proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')]
        )->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payment_proof_rejects_unsupported_file_type(): void
    {
        Storage::fake('public');

        $admin = $this->userWithRole(Role::ADMIN);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $event = $this->makeEvent($admin);
        $registration = $this->makeRegistration($event, $participant);

        $this->actingAs($participant)->post(
            route('payments.store', $registration),
            ['proof_file' => UploadedFile::fake()->create('bukti.txt', 10, 'text/plain')]
        )->assertSessionHasErrors('proof_file');

        $this->assertDatabaseCount('payments', 0);
    }
}
