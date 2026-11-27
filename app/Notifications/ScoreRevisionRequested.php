<?php

namespace App\Notifications;

use App\Models\ScoreRevisionRequest;
use Illuminate\Notifications\Notification;

class ScoreRevisionRequested extends Notification
{
    public function __construct(public ScoreRevisionRequest $revision)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $event = $this->revision->event;
        $participant = $this->revision->score?->registration?->user?->name;

        return [
            'revision_id' => $this->revision->id,
            'event_id' => $event?->id,
            'event_name' => $event?->name,
            'url' => '/jury/revisions',
            'title' => 'Permintaan revisi penilaian',
            'message' => sprintf(
                'Panitia "%s" meminta Anda meninjau ulang penilaian peserta %s. Alasan: %s',
                $event?->name ?? 'event',
                $participant ?? 'peserta',
                $this->revision->reason
            ),
        ];
    }
}
