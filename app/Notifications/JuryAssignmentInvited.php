<?php

namespace App\Notifications;

use App\Models\EventJuryFormAssignment;
use Illuminate\Notifications\Notification;

class JuryAssignmentInvited extends Notification
{
    public function __construct(public EventJuryFormAssignment $assignment)
    {
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
        $event = $this->assignment->event;
        $form = $this->assignment->juryFormTemplate;

        return [
            'assignment_id' => $this->assignment->id,
            'event_id' => $this->assignment->event_id,
            'event_name' => $event?->name,
            'form_name' => $form?->name,
            'jury_role' => $this->assignment->jury_role,
            'organizer_name' => $event?->organizer?->name,
            'url' => route('jury.invitations.index', absolute: false),
            'title' => 'Undangan menjadi juri',
            'message' => sprintf(
                'Panitia "%s" mengundang Anda sebagai juri%s. Setujui undangan agar Anda bisa menilai peserta.',
                $event?->name ?? 'event',
                $this->assignment->jury_role ? ' (' . $this->assignment->jury_role . ')' : ''
            ),
        ];
    }
}
