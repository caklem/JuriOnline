<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreRevisionRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'jury_score_id',
        'event_id',
        'requested_by',
        'jury_id',
        'reason',
        'proposed_payload',
        'status',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'proposed_payload' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function score()
    {
        return $this->belongsTo(JuryScore::class, 'jury_score_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function jury()
    {
        return $this->belongsTo(User::class, 'jury_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
