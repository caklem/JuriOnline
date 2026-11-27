<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiJuknisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeEvent(): array
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->firstOrFail()->id,
            'event_scope' => 'all',
            'is_active' => true,
        ]);
        $category = Category::firstOrCreate(['name' => 'Kompetisi'], ['description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $admin->id,
            'category_id' => $category->id,
            'name' => 'Event Juknis',
            'description' => 'Deskripsi',
            'price' => 0,
            'quota' => 10,
            'registered_count' => 0,
            'registration_start' => '2026-11-01',
            'registration_end' => '2026-11-20',
            'event_date' => '2026-12-01',
            'event_start' => '2026-12-01',
            'event_end' => '2026-12-02',
            'status' => 'active',
        ]);

        return [$admin, $event];
    }

    private function payload(): array
    {
        return [
            'name' => 'Event Juknis',
            'description' => 'Deskripsi',
            'price' => 0,
            'quota' => 10,
            'registration_start' => '2026-11-01',
            'registration_end' => '2026-11-20',
            'event_date' => '2026-12-01',
            'event_start' => '2026-12-01',
            'event_end' => '2026-12-02',
            'status' => 'active',
        ];
    }

    public function test_admin_can_upload_and_remove_juknis(): void
    {
        Storage::fake('public');
        [$admin, $event] = $this->makeEvent();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post("/api/admin/events/{$event->id}", [
                ...$this->payload(),
                'juknis' => UploadedFile::fake()->create('juknis.pdf', 100, 'application/pdf'),
            ]);

        $response->assertOk()->assertJsonPath('event.juknis_name', 'juknis.pdf');
        $this->assertNotNull($event->fresh()->juknis_file);
        Storage::disk('public')->assertExists($event->fresh()->juknis_file);

        $this->getJson("/api/public/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('event.juknis_name', 'juknis.pdf');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->post("/api/admin/events/{$event->id}", [...$this->payload(), 'remove_juknis' => 1])
            ->assertOk();

        $this->assertNull($event->fresh()->juknis_file);
        $this->assertNull($event->fresh()->juknis_name);
    }

    public function test_juknis_rejects_unsupported_file_type(): void
    {
        Storage::fake('public');
        [$admin, $event] = $this->makeEvent();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'])
            ->post("/api/admin/events/{$event->id}", [
                ...$this->payload(),
                'juknis' => UploadedFile::fake()->create('juknis.exe', 10, 'application/octet-stream'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('juknis');
    }
}
