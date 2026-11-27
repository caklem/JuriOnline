<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizerProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Akses ditolak.');
        }

        $profile = $user->organizerProfile;

        return response()->json([
            'profile' => [
                'organizer_name' => $profile?->organizer_name ?? $user->name,
                'description' => $profile?->description,
                'contact_email' => $profile?->contact_email ?? $user->email,
                'phone' => $profile?->phone,
                'logo_url' => $profile?->logo_path ? asset('storage/' . $profile->logo_path) : null,
                'is_complete' => (bool) ($profile?->organizer_name && $profile?->contact_email),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->canManageEvents()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'organizer_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'contact_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        unset($validated['logo']);

        $profile = OrganizerProfile::firstOrNew(['user_id' => $user->id]);

        if ($request->hasFile('logo')) {
            if ($profile->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('organizer-logos', 'public');
        }

        $profile->fill($validated)->save();

        return response()->json([
            'message' => 'Profil organizer tersimpan.',
            'profile' => [
                'organizer_name' => $profile->organizer_name,
                'description' => $profile->description,
                'contact_email' => $profile->contact_email,
                'phone' => $profile->phone,
                'logo_url' => $profile->logo_path ? asset('storage/' . $profile->logo_path) : null,
                'is_complete' => true,
            ],
        ]);
    }
}
