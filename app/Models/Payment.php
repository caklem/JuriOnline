<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'amount',
        'proof_file',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Check if payment is verified
    public function isVerified()
    {
        return $this->status === 'verified';
    }

    // Check if payment is pending
    public function isPending()
    {
        return $this->status === 'pending';
    }
}
