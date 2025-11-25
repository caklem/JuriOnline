<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomEventFormTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'form_fields',
        'created_by'
    ];

    protected $casts = [
        'form_fields' => 'array'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
