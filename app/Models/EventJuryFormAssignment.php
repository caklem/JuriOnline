<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventJuryFormAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'jury_id',
        'jury_form_template_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
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
