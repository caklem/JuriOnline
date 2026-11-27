<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JuryFormTemplateApiTest extends TestCase
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
            'name' => 'Event Template Juri',
            'description' => 'Event pengujian template juri.',
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

        return [$admin, $event, $category];
    }

    public function test_jury_forms_endpoint_returns_builtin_and_custom_templates(): void
    {
        [$admin, $event] = $this->scenario();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/admin/events/{$event->id}/jury-forms")
            ->assertOk();

        $this->assertNotEmpty($response->json('built_in_templates'));
        $this->assertSame('competition_basic', $response->json('built_in_templates.0.key'));
        $this->assertIsArray($response->json('custom_templates'));
    }

    public function test_admin_can_save_custom_jury_form_template(): void
    {
        [$admin, $event] = $this->scenario();
        $token = $admin->createToken('test')->plainTextToken;
        $payload = [
            'name' => 'Penilaian Vokal Daerah',
            'description' => 'Template penilaian lomba vokal.',
            'use_default_score' => true,
            'criteria_fields' => [
                ['label' => 'Teknik Vokal', 'type' => 'score', 'required' => true, 'max' => 40, 'description' => 'Kontrol napas dan intonasi.'],
                ['label' => 'Penghayatan', 'type' => 'score', 'required' => true, 'max' => 60, 'description' => ''],
            ],
        ];

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/jury-forms/save-template", $payload)
            ->assertCreated()
            ->assertJsonPath('template_id', fn ($id) => is_int($id));

        $this->assertDatabaseHas('custom_jury_form_templates', [
            'name' => 'Penilaian Vokal Daerah',
            'created_by' => $admin->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/admin/events/{$event->id}/jury-forms")
            ->assertOk()
            ->assertJsonFragment(['name' => 'Penilaian Vokal Daerah']);
    }

    public function test_custom_template_requires_criteria(): void
    {
        [$admin, $event] = $this->scenario();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/admin/events/{$event->id}/jury-forms/save-template", [
                'name' => 'Kosong',
                'criteria_fields' => [],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('criteria_fields');
    }
}
