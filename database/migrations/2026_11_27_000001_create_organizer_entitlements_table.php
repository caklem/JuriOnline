<?php

use App\Models\OrganizerEntitlement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizer_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // null = tanpa batas (Admin Owner / admin global)
            $table->unsignedInteger('event_limit')->nullable()->default(OrganizerEntitlement::DEFAULT_EVENT_LIMIT);
            $table->json('features')->nullable();
            $table->string('source', 40)->default('core');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });

        // Backfill: Admin Event (scope own) dibatasi 3 event, admin global/owner tanpa batas.
        User::query()->whereHas('role', fn ($query) => $query->whereIn('name', [Role::ADMIN, Role::ADMIN_OWNER]))
            ->each(function (User $user) {
                OrganizerEntitlement::create([
                    'user_id' => $user->id,
                    'event_limit' => $user->isAdminOwner() || !$user->isEventAdmin()
                        ? null
                        : OrganizerEntitlement::DEFAULT_EVENT_LIMIT,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizer_entitlements');
    }
};
