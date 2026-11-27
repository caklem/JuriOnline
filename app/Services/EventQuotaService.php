<?php

namespace App\Services;

use App\Models\Event;
use App\Models\OrganizerEntitlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventQuotaService
{
    /**
     * Batas hanya berlaku bagi Admin Event (role admin, scope own).
     * Admin Owner dan admin global tidak dibatasi.
     */
    public function limitFor(User $user): ?int
    {
        if (!$user->isAdmin() || $user->isAdminOwner() || $user->canManageAllEvents()) {
            return null;
        }

        $entitlement = OrganizerEntitlement::firstOrCreate(
            ['user_id' => $user->id],
            ['event_limit' => OrganizerEntitlement::DEFAULT_EVENT_LIMIT]
        );

        if ($entitlement->valid_until && $entitlement->valid_until->isPast()) {
            return 0;
        }

        return $entitlement->event_limit;
    }

    /**
     * @return array{limit: ?int, used: int, remaining: ?int, can_create: bool, unlimited: bool}
     */
    public function quotaFor(User $user): array
    {
        $limit = $this->limitFor($user);
        $used = Event::where('organizer_id', $user->id)->count();

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $limit === null ? null : max(0, $limit - $used),
            'can_create' => $limit === null || $used < $limit,
            'unlimited' => $limit === null,
        ];
    }

    /**
     * Buat event dengan penguncian per organizer agar dua request paralel
     * tidak bisa melampaui kuota.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(User $user, array $attributes): Event
    {
        return DB::transaction(function () use ($user, $attributes) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $limit = $this->limitFor($lockedUser);
            $used = Event::where('organizer_id', $lockedUser->id)->lockForUpdate()->count();

            if ($limit !== null && $used >= $limit) {
                throw ValidationException::withMessages([
                    'quota' => "Kuota event tercapai. Maksimal {$limit} event tersimpan per akun Admin Event.",
                ]);
            }

            $attributes['organizer_id'] = $lockedUser->id;

            return Event::create($attributes);
        });
    }
}
