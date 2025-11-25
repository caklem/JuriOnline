<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'responses',
    ];

    protected $casts = [
        'responses' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
