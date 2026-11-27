<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventChatSetting;
use App\Models\EventMessage;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventChatController extends Controller
{
    public function settings(Request $request, Event $event): JsonResponse
    {
        $this->guardManager($request, $event);
        $settings = $this->settingsFor($event);

        return response()->json(['settings' => $this->settingsPayload($settings)]);
    }

    public function updateSettings(Request $request, Event $event): JsonResponse
    {
        $this->guardManager($request, $event);

        $validated = $request->validate([
            'enabled' => 'boolean',
            'participants_can_send' => 'boolean',
            'juries_can_send' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'whatsapp_group_url' => 'nullable|url|max:500',
            'announcement' => 'nullable|string|max:500',
        ]);

        $settings = EventChatSetting::updateOrCreate(['event_id' => $event->id], $validated);

        return response()->json([
            'message' => 'Pengaturan chat tersimpan.',
            'settings' => $this->settingsPayload($settings),
        ]);
    }

    public function adminMessages(Request $request, Event $event): JsonResponse
    {
        $this->guardManager($request, $event);

        $messages = $event->messages()
            ->with('sender:id,name,role_id')
            ->latest()
            ->limit(200)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (EventMessage $message) => $this->messagePayload($message, $request, true));

        return response()->json([
            'settings' => $this->settingsPayload($this->settingsFor($event)),
            'data' => $messages,
        ]);
    }

    public function sendAdmin(Request $request, Event $event): JsonResponse
    {
        $this->guardManager($request, $event);

        return $this->storeMessage($request, $event);
    }

    public function hide(Request $request, EventMessage $message): JsonResponse
    {
        return $this->setHidden($request, $message, true);
    }

    public function unhide(Request $request, EventMessage $message): JsonResponse
    {
        return $this->setHidden($request, $message, false);
    }

    private function setHidden(Request $request, EventMessage $message, bool $hidden): JsonResponse
    {
        $this->guardManager($request, $message->event);

        $message->update([
            'is_hidden' => $hidden,
            'hidden_by' => $hidden ? $request->user()->id : null,
            'hidden_at' => $hidden ? now() : null,
        ]);

        return response()->json(['message' => $hidden ? 'Pesan disembunyikan.' : 'Pesan ditampilkan kembali.']);
    }

    public function messages(Request $request, Event $event): JsonResponse
    {
        $access = $this->guardAccess($request, $event);
        $settings = $this->settingsFor($event);

        $messages = $event->messages()
            ->with('sender:id,name,role_id')
            ->when(!$access['is_manager'], fn ($query) => $query->where('is_hidden', false))
            ->latest()
            ->limit(200)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (EventMessage $message) => $this->messagePayload($message, $request, $access['is_manager']));

        return response()->json([
            'event' => ['id' => $event->id, 'name' => $event->name],
            'settings' => $this->settingsPayload($settings),
            'access' => $access,
            'data' => $messages,
        ]);
    }

    public function send(Request $request, Event $event): JsonResponse
    {
        $access = $this->guardAccess($request, $event);
        $settings = $this->settingsFor($event);

        if (!$settings->enabled) {
            return response()->json(['message' => 'Chat event sedang dinonaktifkan panitia.'], 422);
        }

        if (!$access['is_manager']) {
            if ($access['role'] === 'participant' && !$settings->participants_can_send) {
                return response()->json(['message' => 'Peserta tidak diizinkan mengirim pesan saat ini.'], 422);
            }

            if ($access['role'] === 'jury' && !$settings->juries_can_send) {
                return response()->json(['message' => 'Juri tidak diizinkan mengirim pesan saat ini.'], 422);
            }
        }

        return $this->storeMessage($request, $event);
    }

    public function report(Request $request, EventMessage $message): JsonResponse
    {
        $this->guardAccess($request, $message->event);

        $validated = $request->validate(['reason' => 'required|string|max:500']);

        $message->update([
            'reported_at' => now(),
            'reported_by' => $request->user()->id,
            'report_reason' => $validated['reason'],
        ]);

        return response()->json(['message' => 'Pesan dilaporkan ke panitia.']);
    }

    private function storeMessage(Request $request, Event $event): JsonResponse
    {
        $settings = $this->settingsFor($event);

        if (!$settings->enabled) {
            return response()->json(['message' => 'Chat event belum diaktifkan panitia.'], 422);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:2000',
        ]);

        $message = $event->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $message->load('sender:id,name,role_id');

        return response()->json([
            'message' => 'Pesan terkirim.',
            'data' => $this->messagePayload($message, $request, true),
        ], 201);
    }

    private function guardManager(Request $request, Event $event): void
    {
        $user = $request->user();

        if (!$user->canManageEvents() || !$user->canManageEvent($event)) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * @return array{is_manager: bool, role: string, can_send: bool, registered: bool}
     */
    private function guardAccess(Request $request, Event $event): array
    {
        $user = $request->user();
        $isManager = $user->canManageEvents() && $user->canManageEvent($event);
        $registered = Registration::where('event_id', $event->id)->where('user_id', $user->id)->exists();
        $isJury = $user->isJury() && $user->hasAcceptedAssignmentFor($event);

        if (!$isManager && !$registered && !$isJury) {
            abort(403, 'Anda tidak memiliki akses ke chat event ini.');
        }

        $settings = $this->settingsFor($event);

        return [
            'is_manager' => $isManager,
            'role' => $isManager ? 'admin' : ($isJury ? 'jury' : 'participant'),
            'registered' => $registered,
            'can_send' => $settings->enabled && (
                $isManager
                || ($registered && $settings->participants_can_send)
                || ($isJury && $settings->juries_can_send)
            ),
        ];
    }

    private function settingsFor(Event $event): EventChatSetting
    {
        return EventChatSetting::firstOrCreate(['event_id' => $event->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsPayload(EventChatSetting $settings): array
    {
        return [
            'enabled' => $settings->enabled,
            'participants_can_send' => $settings->participants_can_send,
            'juries_can_send' => $settings->juries_can_send,
            'whatsapp_enabled' => $settings->whatsapp_enabled,
            'whatsapp_group_url' => $settings->whatsapp_group_url,
            'announcement' => $settings->announcement,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(EventMessage $message, Request $request, bool $includeHidden): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'sender' => [
                'id' => $message->sender?->id,
                'name' => $message->sender?->name ?? 'Pengguna',
                'role' => $message->sender?->role?->name,
            ],
            'is_mine' => $message->sender_id === $request->user()->id,
            'created_at' => $message->created_at?->toIso8601String(),
            'reported' => $message->reported_at !== null,
            'report_reason' => $includeHidden ? $message->report_reason : null,
            'is_hidden' => $includeHidden ? $message->is_hidden : false,
        ];
    }
}
