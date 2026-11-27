<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'phone',
        'organization',
        'expertise',
        'bio',
        'whatsapp_number',
        'whatsapp_consent',
        'event_scope',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Default attribute values for new instances.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function organizerProfile()
    {
        return $this->hasOne(OrganizerProfile::class);
    }

    public function entitlement()
    {
        return $this->hasOne(OrganizerEntitlement::class);
    }

    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function juryScores()
    {
        return $this->hasMany(JuryScore::class, 'jury_id');
    }

    public function juryFormAssignments()
    {
        return $this->hasMany(EventJuryFormAssignment::class, 'jury_id');
    }

    // Assignment yang sudah disetujui juri (boleh menilai)
    public function acceptedJuryAssignments()
    {
        return $this->juryFormAssignments()->accepted();
    }

    // Undangan yang belum dijawab juri
    public function pendingJuryAssignments()
    {
        return $this->juryFormAssignments()->pending();
    }

    public function hasAcceptedAssignmentFor(Event $event): bool
    {
        return $this->acceptedJuryAssignments()
            ->where('event_id', $event->id)
            ->exists();
    }

    // Get assigned events for this jury (hanya yang sudah disetujui)
    public function assignedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_jury_form_assignments', 'jury_id', 'event_id')
            ->withPivot(['jury_form_template_id', 'status'])
            ->wherePivot('status', EventJuryFormAssignment::STATUS_ACCEPTED)
            ->withTimestamps();
    }

    // Get assigned form template for a specific event (hanya bila sudah disetujui)
    public function getAssignedFormTemplate(Event $event)
    {
        $assignment = $this->acceptedJuryAssignments()
            ->where('event_id', $event->id)
            ->first();

        return $assignment ? $assignment->juryFormTemplate : null;
    }

    // Role checking methods
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isAdminOwner()
    {
        return $this->hasRole(Role::ADMIN_OWNER);
    }

    public function isAdmin()
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isParticipant()
    {
        return $this->hasRole(Role::PARTICIPANT);
    }

    public function isJury()
    {
        return $this->hasRole(Role::JURY);
    }

    public function isActive()
    {
        return $this->is_active !== false;
    }

    public function isAdminOrOwner()
    {
        return $this->isAdmin() || $this->isAdminOwner();
    }

    public function isEventAdmin(): bool
    {
        return $this->isAdmin() && $this->event_scope === 'own';
    }

    public function canManageAllEvents(): bool
    {
        return $this->isAdminOwner() || ($this->isAdmin() && $this->event_scope !== 'own');
    }

    public function canManageEvents(): bool
    {
        return $this->isAdminOwner() || $this->isAdmin();
    }

    public function canManageEvent(Event $event): bool
    {
        return $this->isAdminOwner()
            || ($this->isAdmin() && ($this->canManageAllEvents() || $event->organizer_id === $this->id));
    }

    public function canVerifyPayments(): bool
    {
        return $this->isAdminOwner() || $this->isAdmin();
    }

    public function canViewAllScores(): bool
    {
        return $this->canManageAllEvents();
    }

    public function roleLabel(): string
    {
        if ($this->isAdminOwner()) {
            return 'Admin Owner';
        }

        if ($this->isAdmin()) {
            return $this->isEventAdmin() ? 'Admin Event' : 'Admin';
        }

        if ($this->isParticipant()) {
            return 'Peserta';
        }

        if ($this->isJury()) {
            return 'Juri';
        }

        return 'Pengguna';
    }
}
