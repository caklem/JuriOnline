<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventForm;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationFileTest extends TestCase
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
        $participant = User::factory()->create([
            'role_id' => Role::where('name', Role::PARTICIPANT)->firstOrFail()->id,
        ]);
        $other = User::factory()->create([
            'role_id' => Role::where('name', Role::PARTICIPANT)->firstOrFail()->id,
        ]);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Berkas',
            'description' => 'Event pengujian berkas.',
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

        EventForm::create([
            'event_id' => $event->id,
            'description' => 'Form berkas',
            'form_fields' => [
                ['label' => 'Kartu Pelajar', 'type' => 'file', 'required' => true],
                ['label' => 'Nama Sekolah', 'type' => 'text', 'required' => true],
            ],
        ]);

        return [$admin, $participant, $other, $event];
    }

    public function test_participant_file_is_stored_privately_and_can_be_downloaded_by_owner(): void
    {
        Storage::fake('local');
        [$admin, $participant, $other, $event] = $this->scenario();
        $token = $participant->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post("/api/events/{$event->id}/register", [
                'field_0' => UploadedFile::fake()->create('kartu.pdf', 120, 'application/pdf'),
                'field_1' => 'SMA Satu',
            ])
            ->assertCreated();

        $registrationId = $response->json('registration_id');
        $this->assertDatabaseHas('registration_files', ['registration_id' => $registrationId]);
        $file = \App\Models\RegistrationFile::firstOrFail();
        Storage::disk('local')->assertExists($file->path);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->get("/api/registrations/{$registrationId}/files/{$file->id}")
            ->assertOk();

        $otherToken = $other->createToken('other')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$otherToken}")
            ->get("/api/registrations/{$registrationId}/files/{$file->id}")
            ->assertForbidden();

        $adminToken = $admin->createToken('admin')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->get("/api/registrations/{$registrationId}/files/{$file->id}")
            ->assertOk();
    }

    public function test_file_field_requires_a_valid_file(): void
    {
        [, $participant, , $event] = $this->scenario();
        $token = $participant->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/events/{$event->id}/register", [
                'field_0' => 'bukan file',
                'field_1' => 'SMA Satu',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('field_0');
    }
}
