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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerRoleTest extends TestCase
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

    public function test_guest_is_redirected_from_owner_area(): void
    {
        $this->get('/owner/users')->assertRedirect('/login');
    }

    public function test_owner_sees_full_monitoring_dashboard(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);
        $category = Category::create(['name' => 'Kompetisi', 'description' => 'Lomba']);

        $event = Event::create([
            'organizer_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Lomba Debat',
            'description' => 'Debat antar sekolah',
            'price' => 100000,
            'quota' => 20,
            'registration_start' => now()->subWeek(),
            'registration_end' => now()->addWeek(),
            'event_start' => now()->addMonth(),
            'event_end' => now()->addMonth()->addDay(),
            'status' => 'active',
        ]);

        $participant = $this->userWithRole(Role::PARTICIPANT);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'registration_number' => 'REG-TEST-0001',
            'status' => 'verified',
            'registered_at' => now(),
        ]);

        Payment::create([
            'registration_id' => $registration->id,
            'amount' => 100000,
            'proof_file' => 'payment-proofs/bukti.jpg',
            'status' => 'verified',
            'verified_by' => $owner->id,
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk()
            ->assertViewIs('dashboard.admin-owner')
            ->assertSee('Performa Organizer')
            ->assertSee('Distribusi Role')
            ->assertSee('Tren Pendaftaran')
            ->assertSee('Event Terpopuler')
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Lomba Debat')
            ->assertSee($owner->name)
            ->assertViewHas('totalEvents', 1)
            ->assertViewHas('totalRegistrations', 1)
            ->assertViewHas('totalUsers', 2);

        $this->assertEquals(100000, (float) $response->viewData('totalRevenue'));
        $this->assertSame($owner->name, $response->viewData('organizerStats')->first()->name);
        $this->assertEquals(1, (int) $response->viewData('organizerStats')->first()->events_count);
        $this->assertEquals(100000, (float) $response->viewData('organizerStats')->first()->revenue);
    }

    public function test_non_owner_roles_cannot_access_owner_area(): void
    {
        foreach ([Role::ADMIN, Role::PARTICIPANT, Role::JURY] as $roleName) {
            $user = $this->userWithRole($roleName);

            $this->actingAs($user)->get('/owner/users')->assertForbidden();
            $this->actingAs($user)->post('/owner/users', [])->assertForbidden();
        }
    }

    public function test_owner_can_search_and_filter_users(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);
        $this->userWithRole(Role::PARTICIPANT, ['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        $this->userWithRole(Role::JURY, ['name' => 'Sari Juri', 'email' => 'sari@example.com']);

        $this->actingAs($owner)
            ->get('/owner/users?q=Budi')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Sari Juri');

        $this->actingAs($owner)
            ->get('/owner/users?role=jury')
            ->assertOk()
            ->assertSee('Sari Juri')
            ->assertDontSee('Budi Santoso');
    }

    public function test_owner_can_create_user_with_role(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);
        $juryRole = Role::where('name', Role::JURY)->firstOrFail();

        $response = $this->actingAs($owner)->post('/owner/users', [
            'name' => 'Juri Baru',
            'email' => 'juri.baru@example.com',
            'role_id' => $juryRole->id,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('owner.users.index'))->assertSessionHasNoErrors();

        $created = User::where('email', 'juri.baru@example.com')->firstOrFail();

        $this->assertSame($juryRole->id, $created->role_id);
        $this->assertTrue($created->isActive());
        $this->assertTrue(Hash::check('rahasia123', $created->password));
    }

    public function test_creating_user_rejects_duplicate_email_and_unknown_role(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);

        $this->actingAs($owner)->post('/owner/users', [
            'name' => 'Duplikat',
            'email' => $owner->email,
            'role_id' => 9999,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors(['email', 'role_id']);

        $this->assertSame(1, User::where('email', $owner->email)->count());
    }

    public function test_owner_can_change_another_users_role(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);
        $participant = $this->userWithRole(Role::PARTICIPANT);
        $juryRole = Role::where('name', Role::JURY)->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('owner.users.role', $participant), ['role_id' => $juryRole->id])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($juryRole->id, $participant->fresh()->role_id);
    }

    public function test_owner_cannot_change_own_role(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);
        $participantRole = Role::where('name', Role::PARTICIPANT)->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('owner.users.role', $owner), ['role_id' => $participantRole->id])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(Role::ADMIN_OWNER, $owner->fresh()->role->name);
    }

    public function test_owner_can_deactivate_and_reactivate_user(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);
        $participant = $this->userWithRole(Role::PARTICIPANT);

        $this->actingAs($owner)
            ->patch(route('owner.users.status', $participant))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($participant->fresh()->isActive());

        $this->actingAs($owner)->patch(route('owner.users.status', $participant->fresh()));

        $this->assertTrue($participant->fresh()->isActive());
    }

    public function test_owner_cannot_deactivate_own_account(): void
    {
        $owner = $this->userWithRole(Role::ADMIN_OWNER);

        $this->actingAs($owner)
            ->patch(route('owner.users.status', $owner))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($owner->fresh()->isActive());
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = $this->userWithRole(Role::PARTICIPANT, [
            'email' => 'nonaktif@example.com',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_session_is_terminated(): void
    {
        $user = $this->userWithRole(Role::PARTICIPANT, ['is_active' => false]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
