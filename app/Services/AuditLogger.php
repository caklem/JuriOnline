<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function log(string $action, ?Model $auditable = null, array $metadata = [], ?Request $request = null): AuditLog
    {
        return AuditLog::create([
            'actor_id' => $request?->user()?->id ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
