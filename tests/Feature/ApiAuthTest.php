<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function user(string $role, array $attributes = []): User
    {
        $roleModel = Role::where('name', $role)->firstOrFail();

        return User::factory()->create(array_merge([
            'role_id' => $roleModel->id,
            'is_active' => true,
        ], $attributes));
    }

    public function test_login_returns_token_and_role_label(): void
    {
        $user = $this->user(Role::JURY, ['email' => 'juri.api@example.com', 'password' => 'rahasia123']);

        $response = $this->postJson('/api/login', [
            'email' => 'juri.api@example.com',
            'password' => 'rahasia123',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.role_label', 'Juri')
            ->assertJsonStructure(['token']);
    }

    public function test_login_rejects_wrong_password_and_inactive_account(): void
    {
        $this->user(Role::PARTICIPANT, ['email' => 'peserta.api@example.com', 'password' => 'rahasia123']);

        $this->postJson('/api/login', [
            'email' => 'peserta.api@example.com',
            'password' => 'salah',
        ])->assertStatus(422)->assertJsonValidationErrors('email');

        $this->user(Role::PARTICIPANT, [
            'email' => 'nonaktif.api@example.com',
            'password' => 'rahasia123',
            'is_active' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => 'nonaktif.api@example.com',
            'password' => 'rahasia123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_register_creates_participant_with_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Peserta Baru',
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertOk()->assertJsonPath('user.role_label', 'Peserta')->assertJsonStructure(['token']);
        $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);
    }

    public function test_register_jury_creates_jury_account(): void
    {
        $response = $this->postJson('/api/register/jury', [
            'name' => 'Juri Baru',
            'email' => 'juribaru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertOk()->assertJsonPath('user.role_label', 'Juri');
    }

    public function test_profile_can_be_updated_and_password_changed(): void
    {
        $user = $this->user(Role::PARTICIPANT, ['email' => 'profil.api@example.com', 'password' => 'rahasia123']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson('/api/profile', ['name' => 'Nama Baru', 'email' => 'profil.api@example.com'])
            ->assertOk();

        $this->assertSame('Nama Baru', $user->fresh()->name);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/profile/password', [
                'current_password' => 'rahasia123',
                'password' => 'baru12345',
                'password_confirmation' => 'baru12345',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('baru12345', $user->fresh()->password));
    }
}
