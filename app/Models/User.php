<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
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

    // Get assigned events for this jury
    public function assignedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_jury_form_assignments', 'jury_id', 'event_id')
            ->withPivot('jury_form_template_id')
            ->withTimestamps();
    }

    // Get assigned form template for a specific event
    public function getAssignedFormTemplate(Event $event)
    {
        $assignment = $this->juryFormAssignments()
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

    public function isOrganizer()
    {
        return $this->hasRole(Role::ORGANIZER);
    }

    public function isParticipant()
    {
        return $this->hasRole(Role::PARTICIPANT);
    }

    public function isJury()
    {
        return $this->hasRole(Role::JURY);
    }

    public function canManageEvents()
    {
        return $this->isAdminOwner() || $this->isAdmin() || $this->isOrganizer();
    }

    public function canVerifyPayments()
    {
        return $this->isAdminOwner() || $this->isAdmin() || $this->isOrganizer();
    }
}
