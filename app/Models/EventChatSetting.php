<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventChatSetting extends Model
{
    protected $fillable = [
        'event_id',
        'enabled',
        'participants_can_send',
        'juries_can_send',
        'whatsapp_enabled',
        'whatsapp_group_url',
        'announcement',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'participants_can_send' => 'boolean',
        'juries_can_send' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
