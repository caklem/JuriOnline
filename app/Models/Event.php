<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = static::uniqueSlug($event->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'event';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . (++$suffix);
        }

        return $slug;
    }

    protected $fillable = [
        'organizer_id',
        'category_id',
        'name',
        'slug',
        'description',
        'format',
        'participation_mode',
        'team_min_members',
        'team_max_members',
        'timezone',
        'contact_name',
        'contact_email',
        'contact_phone',
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
        'archived_at',
        'evaluations_published_at',
        'location',
        'banner',
        'juknis_file',
        'juknis_name',
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
        'archived_at' => 'datetime',
        'team_min_members' => 'integer',
        'team_max_members' => 'integer',
        'evaluations_published_at' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function chatSetting()
    {
        return $this->hasOne(EventChatSetting::class);
    }

    public function messages()
    {
        return $this->hasMany(EventMessage::class);
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
        return $this->archived_at === null
            && in_array($this->status, ['active', 'registration_open'], true)
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
