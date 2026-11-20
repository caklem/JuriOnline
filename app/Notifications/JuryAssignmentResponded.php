<?php

namespace App\Notifications;

use App\Models\EventJuryFormAssignment;
use Illuminate\Notifications\Notification;

class JuryAssignmentResponded extends Notification
{
    public function __construct(
        public EventJuryFormAssignment $assignment,
        public string $response,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $accepted = $this->response === EventJuryFormAssignment::STATUS_ACCEPTED;

        return [
            'assignment_id' => $this->assignment->id,
            'event_id' => $this->assignment->event_id,
            'event_name' => $this->assignment->event?->name,
            'jury_name' => $this->assignment->jury?->name,
            'jury_role' => $this->assignment->jury_role,
            'response' => $this->response,
            'url' => route('events.jury-assignments.index', $this->assignment->event_id, absolute: false),
            'title' => $accepted ? 'Juri menerima undangan' : 'Juri menolak undangan',
            'message' => sprintf(
                '%s %s undangan menjadi juri%s di event "%s".',
                $this->assignment->jury?->name ?? 'Juri',
                $accepted ? 'menerima' : 'menolak',
                $this->assignment->jury_role ? ' (' . $this->assignment->jury_role . ')' : '',
                $this->assignment->event?->name ?? 'event'
            ),
        ];
    }
}
