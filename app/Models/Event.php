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
        'event_date',
        'scoring_enabled',
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
        'event_date' => 'date',
        'scoring_enabled' => 'boolean',
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

    /**
     * Apakah juri boleh menilai peserta saat ini?
     * Nilai manual (true/false) selalu menang; null = otomatis sejak hari H.
     */
    public function isScoringOpen(): bool
    {
        if ($this->scoring_enabled === true) {
            return true;
        }

        if ($this->scoring_enabled === false) {
            return false;
        }

        $day = $this->scoringOpensAt();

        return $day !== null
            && Carbon::now()->startOfDay()->greaterThanOrEqualTo($day->copy()->startOfDay());
    }

    /**
     * 'open' (dibuka panitia) | 'closed' (ditutup panitia) | 'auto' (mulai hari H).
     */
    public function scoringMode(): string
    {
        return match ($this->scoring_enabled) {
            true => 'open',
            false => 'closed',
            default => 'auto',
        };
    }

    public function scoringOpensAt(): ?Carbon
    {
        return $this->event_date ?? $this->event_start;
    }

    public function scoringStatusLabel(): string
    {
        if ($this->isScoringOpen()) {
            return $this->scoringMode() === 'auto' ? 'Dibuka otomatis pada hari H' : 'Dibuka oleh panitia';
        }

        return $this->scoringMode() === 'closed'
            ? 'Ditutup oleh panitia'
            : 'Menunggu hari H';
    }
}
