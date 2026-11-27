<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'organizer_name',
        'description',
        'contact_email',
        'phone',
        'logo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
