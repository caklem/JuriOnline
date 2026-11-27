<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\JuryFormTemplate;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JuryAssignmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function scenario(): array
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->firstOrFail()->id,
            'event_scope' => 'own',
        ]);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Alur Juri',
            'description' => 'Event pengujian alur juri.',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'event_date' => now()->addDay(),
            'event_start' => now()->addDay(),
            'event_end' => now()->addDays(2),
            'status' => 'active',
        ]);

        $form = JuryFormTemplate::create([
            'event_id' => $event->id,
            'name' => 'Form Alur Juri',
            'criteria_fields' => [
                ['label' => 'Kualitas', 'type' => 'score', 'required' => true, 'max' => 100],
            ],
            'use_default_score' => true,
        ]);

        return [$admin, $event, $form];
    }

    public function test_admin_can_create_account_with_custom_password(): void
    {
        [$admin, $event, $form] = $this->scenario();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/jury-accounts", [
                'name' => 'Juri Password Custom',
                'email' => 'jury.custom@example.test',
                'jury_form_template_id' => $form->id,
                'password' => 'rahasia12345',
            ])
            ->assertCreated()
            ->assertJsonPath('credentials.code', 'rahasia12345');

        $jury = User::where('email', 'jury.custom@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia12345', $jury->password));
        $this->assertDatabaseHas('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $jury->id,
        ]);
    }

    public function test_admin_event_can_add_existing_jury_created_by_another_admin(): void
    {
        [$admin, $event, $form] = $this->scenario();
        $otherAdmin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->firstOrFail()->id,
            'event_scope' => 'own',
        ]);
        $jury = User::factory()->create([
            'role_id' => Role::where('name', Role::JURY)->firstOrFail()->id,
            'created_by' => $otherAdmin->id,
        ]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/admin/events/{$event->id}/jury-assignments")
            ->assertOk()
            ->assertJsonFragment(['email' => $jury->email]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/jury-assignments", [
                'jury_ids' => [$jury->id],
                'jury_form_template_id' => $form->id,
                'jury_role' => 'Juri Tamu',
            ])
            ->assertOk();

        $this->assertDatabaseHas('event_jury_form_assignments', [
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_role' => 'Juri Tamu',
        ]);
    }

    public function test_reset_password_returns_copyable_credentials(): void
    {
        [$admin, $event, $form] = $this->scenario();
        $jury = User::factory()->create([
            'role_id' => Role::where('name', Role::JURY)->firstOrFail()->id,
            'created_by' => $admin->id,
        ]);
        $assignment = \App\Models\EventJuryFormAssignment::create([
            'event_id' => $event->id,
            'jury_id' => $jury->id,
            'jury_form_template_id' => $form->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/jury-assignments/{$assignment->id}/reset-password", [
                'password' => 'passwordbaru9',
            ])
            ->assertOk()
            ->assertJsonPath('credentials.code', 'passwordbaru9');

        $this->assertTrue(Hash::check('passwordbaru9', $jury->fresh()->password));
    }

    public function test_create_new_account_rejects_existing_email_with_guidance(): void
    {
        [$admin, $event, $form] = $this->scenario();
        $existing = User::factory()->create([
            'role_id' => Role::where('name', Role::JURY)->firstOrFail()->id,
        ]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/jury-accounts", [
                'name' => 'Duplikat',
                'email' => $existing->email,
                'jury_form_template_id' => $form->id,
                'password' => 'password123',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', fn ($message) => str_contains($message, 'Tambah Akun Juri yang Sudah Ada'));
    }
}
