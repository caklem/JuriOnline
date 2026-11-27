<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventChatSetting;
use App\Models\EventJuryFormAssignment;
use App\Models\JuryFormTemplate;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventChatTest extends TestCase
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
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $outsider = $this->userWithRole(Role::PARTICIPANT);
        $jury = $this->userWithRole(Role::JURY);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Chat',
            'description' => 'Event chat.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 1,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_date' => now()->addDay(),
            'event_start' => now()->addDay(),
            'event_end' => now()->addDays(2),
            'status' => 'active',
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-CHAT-1',
            'status' => 'verified',
            'registered_at' => now(),
        ]);

        $form = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Chat',
            'criteria_fields' => [],
            'use_default_score' => false,
        ]);

        EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        EventChatSetting::create(['event_id' => $event->id, 'enabled' => true]);

        return [$admin, $participant, $outsider, $jury, $event];
    }

    public function test_only_registered_participant_or_accepted_jury_can_access_chat(): void
    {
        [, , $outsider, , $event] = $this->scenario();
        $token = $outsider->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/events/{$event->id}/chat")
            ->assertForbidden();
    }

    public function test_participant_can_send_report_and_admin_can_hide_message(): void
    {
        [$admin, $participant, , , $event] = $this->scenario();
        $participantToken = $participant->createToken('participant')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$participantToken}")
            ->postJson("/api/events/{$event->id}/chat", ['body' => 'Halo panitia, jadwal mundur?'])
            ->assertCreated();

        $messageId = $response->json('data.id');

        $this->withHeader('Authorization', "Bearer {$participantToken}")
            ->postJson("/api/messages/{$messageId}/report", ['reason' => 'Informasi perlu konfirmasi'])
            ->assertOk();

        $this->assertDatabaseHas('event_messages', ['id' => $messageId, 'reported_by' => $participant->id]);

        $adminToken = $admin->createToken('admin')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/admin/messages/{$messageId}/hide")
            ->assertOk();

        $this->assertDatabaseHas('event_messages', ['id' => $messageId, 'is_hidden' => true]);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$participantToken}")
            ->getJson("/api/events/{$event->id}/chat")
            ->assertJsonCount(0, 'data');
    }

    public function test_chat_can_be_disabled_by_organizer(): void
    {
        [$admin, $participant, , , $event] = $this->scenario();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->putJson("/api/admin/events/{$event->id}/chat-settings", ['enabled' => false])
            ->assertOk();

        $participantToken = $participant->createToken('participant')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$participantToken}")
            ->postJson("/api/events/{$event->id}/chat", ['body' => 'Halo'])
            ->assertStatus(422);
    }
}
