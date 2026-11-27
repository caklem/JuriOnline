<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_name',
        'team_members',
        'event_id',
        'user_id',
        'registration_number',
        'status',
        'notes',
        'registered_at',
    ];

    protected $casts = [
        'team_members' => 'array',
        'registered_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function juryScores()
    {
        return $this->hasMany(JuryScore::class);
    }

    public function files()
    {
        return $this->hasMany(RegistrationFile::class);
    }

    public function response()
    {
        return $this->hasOne(RegistrationResponse::class);
    }

    // Generate unique registration number
    public static function generateRegistrationNumber($eventId)
    {
        $prefix = 'REG';
        $date = now()->format('Ymd');
        $eventCode = str_pad($eventId, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$eventCode}-{$random}";
    }

    // Get average score
    public function getAverageScore()
    {
        return $this->juryScores()->avg('score') ?? 0;
    }
}
