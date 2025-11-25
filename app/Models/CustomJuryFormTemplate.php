<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomJuryFormTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'criteria_fields',
        'use_default_score',
        'created_by'
    ];

    protected $casts = [
        'criteria_fields' => 'array',
        'use_default_score' => 'boolean'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
