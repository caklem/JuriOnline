<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationFile extends Model
{
    protected $fillable = [
        'registration_id',
        'field_label',
        'path',
        'original_name',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
