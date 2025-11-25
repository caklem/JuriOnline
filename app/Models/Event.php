<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'category_id',
        'name',
        'description',
        'price',
        'quota',
        'registered_count',
        'registration_start',
        'registration_end',
        'event_start',
        'event_end',
        'status',
        'location',
        'banner',
        'require_official_email',
        'allowed_email_domains',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'event_start' => 'date',
        'event_end' => 'date',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function eventForm()
    {
        return $this->hasOne(EventForm::class);
    }

    public function juryFormTemplate()
    {
        return $this->hasOne(JuryFormTemplate::class); // Keep for backward compatibility
    }

    public function juryFormTemplates()
    {
        return $this->hasMany(JuryFormTemplate::class);
    }

    public function juryFormAssignments()
    {
        return $this->hasMany(EventJuryFormAssignment::class);
    }

    // Get assigned juries for this event
    public function assignedJuries()
    {
        return $this->belongsToMany(User::class, 'event_jury_form_assignments', 'event_id', 'jury_id')
            ->withPivot('jury_form_template_id')
            ->withTimestamps();
    }

    // Check if registration is open
    public function isRegistrationOpen()
    {
        $now = Carbon::now();
        return $this->status === 'active'
            && $now->between($this->registration_start, $this->registration_end)
            && $this->registered_count < $this->quota;
    }

    // Check if event is active
    public function isActive()
    {
        $now = Carbon::now();
        return $this->status === 'active'
            && $now->lte($this->event_end);
    }

    // Get available slots
    public function getAvailableSlots()
    {
        return max(0, $this->quota - $this->registered_count);
    }
}
