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
use App\Notifications\ScoreRevisionRequested;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ScoreRevisionTest extends TestCase
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

    private function scenario(): array
    {
        $admin = $this->userWithRole(Role::ADMIN, ['event_scope' => 'own']);
        $jury = $this->userWithRole(Role::JURY);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Revisi',
            'description' => 'Event pengujian revisi skor.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 1,
            'registration_start' => now()->subWeek(),
            'registration_end' => now()->addWeek(),
            'event_date' => now()->toDateString(),
            'event_start' => now()->toDateString(),
            'event_end' => now()->toDateString(),
            'status' => 'active',
            'scoring_enabled' => true,
        ]);

        $form = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Revisi',
            'criteria_fields' => [
                ['label' => 'Kreativitas', 'type' => 'score', 'required' => true, 'max' => 100],
            ],
            'use_default_score' => true,
        ]);

        EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-REVISI-1',
            'status' => 'verified',
            'registered_at' => now(),
        ]);

        $score = JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'score' => 70,
            'public_feedback' => 'Baik',
            'criteria_responses' => ['Kreativitas' => ['score' => 70, 'max' => 100]],
        ]);

        return [$admin, $jury, $event, $form, $score];
    }

    public function test_admin_cannot_edit_jury_score_directly(): void
    {
        [$admin, , , , $score] = $this->scenario();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/jury/scores/{$score->id}", ['score' => 95])
            ->assertForbidden();

        $this->assertSame('70.00', $score->fresh()->score);
    }

    public function test_admin_must_request_revision_and_jury_resolves_it_by_editing(): void
    {
        Notification::fake();
        [$admin, $jury, $event, $form, $score] = $this->scenario();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/admin/scores/{$score->id}/revision-request", [
                'reason' => 'Nilai kreativitas terlihat tidak konsisten dengan rubrik.',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('score_revision_requests', [
            'jury_score_id' => $score->id,
            'jury_id' => $jury->id,
            'status' => 'pending',
        ]);
        Notification::assertSentTo($jury, ScoreRevisionRequested::class);

        $juryToken = $jury->createToken('jury')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$juryToken}")
            ->getJson('/api/jury/revisions')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withHeader('Authorization', "Bearer {$juryToken}")
            ->patchJson("/api/jury/scores/{$score->id}", [
                'score' => 85,
                'public_feedback' => 'Diperbaiki',
                'criteria_0' => 85,
            ])
            ->assertOk();

        $this->assertSame('85.00', $score->fresh()->score);
        $this->assertDatabaseHas('score_revision_requests', [
            'jury_score_id' => $score->id,
            'status' => 'applied',
            'resolved_by' => $jury->id,
        ]);
    }

    public function test_jury_can_reject_revision_with_notes(): void
    {
        [$admin, $jury, , , $score] = $this->scenario();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/admin/scores/{$score->id}/revision-request", [
                'reason' => 'Mohon dicek kembali nilai kreativitas peserta ini.',
            ])
            ->assertCreated();

        $revisionId = \App\Models\ScoreRevisionRequest::firstOrFail()->id;
        $juryToken = $jury->createToken('jury')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$juryToken}")
            ->postJson("/api/jury/revisions/{$revisionId}/reject", [
                'notes' => 'Nilai sudah sesuai rubrik dan bukti penilaian.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('score_revision_requests', [
            'id' => $revisionId,
            'status' => 'rejected',
            'resolved_by' => $jury->id,
        ]);
    }
}
