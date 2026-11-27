<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuryScore extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (JuryScore $score) {
            if (empty($score->status)) {
                $score->status = 'submitted';
            }

            if (empty($score->submitted_at)) {
                $score->submitted_at = now();
            }
        });
    }

    protected $fillable = [
        'status',
        'submitted_at',
        'published_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'registration_id',
        'jury_id',
        'jury_form_template_id',
        'score',
        'public_feedback',
        'private_evaluation',
        'criteria_responses',
        'form_snapshot',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'score' => 'decimal:2',
        'criteria_responses' => 'array',
        'form_snapshot' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function jury()
    {
        return $this->belongsTo(User::class, 'jury_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function juryFormTemplate()
    {
        return $this->belongsTo(JuryFormTemplate::class);
    }
}
