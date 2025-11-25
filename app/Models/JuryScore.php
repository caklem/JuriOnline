<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuryScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'jury_id',
        'jury_form_template_id',
        'score',
        'public_feedback',
        'private_evaluation',
        'criteria_responses',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'criteria_responses' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function jury()
    {
        return $this->belongsTo(User::class, 'jury_id');
    }

    public function juryFormTemplate()
    {
        return $this->belongsTo(JuryFormTemplate::class);
    }
}
