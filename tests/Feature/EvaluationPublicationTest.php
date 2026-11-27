<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\JuryFormTemplate;
use App\Models\JuryScore;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::where('name', $role)->firstOrFail()->id,
        ], $attributes));
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
            'name' => 'Event Publikasi',
            'description' => 'Event pengujian publikasi evaluasi.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 1,
            'registration_start' => now()->subWeek(),
            'registration_end' => now()->addWeek(),
            'event_date' => now(),
            'event_start' => now(),
            'event_end' => now(),
            'status' => 'active',
            'scoring_enabled' => true,
        ]);

        $form = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Publikasi',
            'criteria_fields' => [['label' => 'Kualitas', 'type' => 'score', 'required' => true, 'max' => 100]],
            'use_default_score' => true,
        ]);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-PUB-1',
            'status' => 'verified',
            'registered_at' => now(),
        ]);

        $score = JuryScore::create([
            'registration_id' => $registration->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'score' => 88,
            'public_feedback' => 'Presentasi sangat baik.',
            'private_evaluation' => 'Catatan internal panitia.',
        ]);

        return [$admin, $jury, $participant, $event, $registration, $score];
    }

    public function test_unpublished_evaluation_is_hidden_from_participant_results(): void
    {
        [, , $participant, , $registration] = $this->scenario();
        $token = $participant->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/registrations/{$registration->id}/results")
            ->assertOk()
            ->assertJsonPath('published', false)
            ->assertJsonPath('average_score', null)
            ->assertJsonCount(0, 'evaluations');
    }

    public function test_review_then_publish_exposes_evaluation_without_private_notes(): void
    {
        [$admin, , $participant, , $registration, $score] = $this->scenario();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/admin/scores/{$score->id}/publish")
            ->assertStatus(422);

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/admin/scores/{$score->id}/review", ['status' => 'reviewed', 'notes' => 'Sesuai rubrik.'])
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/admin/scores/{$score->id}/publish")
            ->assertOk();

        $this->assertDatabaseHas('jury_scores', ['id' => $score->id, 'status' => 'published']);
        $this->assertNotNull($score->refresh()->published_at);

        $participantToken = $participant->createToken('participant')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $response = $this->withHeader('Authorization', "Bearer {$participantToken}")
            ->getJson("/api/registrations/{$registration->id}/results")
            ->assertOk()
            ->assertJsonPath('published', true)
            ->assertJsonPath('average_score', 88)
            ->assertJsonPath('rank', 1);

        $this->assertStringNotContainsString('Catatan internal', $response->getContent());
    }

    public function test_bulk_publish_requires_all_scores_reviewed(): void
    {
        [$admin, , , $event, , $score] = $this->scenario();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/evaluations/publish")
            ->assertStatus(422);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/scores/{$score->id}/review", ['status' => 'reviewed'])
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/evaluations/publish")
            ->assertOk();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
        $this->assertNotNull($event->refresh()->evaluations_published_at);
    }
}
