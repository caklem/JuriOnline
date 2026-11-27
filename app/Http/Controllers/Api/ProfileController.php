<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_label' => $user->roleLabel(),
                'jury_profile' => $user->isJury() ? [
                    'phone' => $user->phone,
                    'organization' => $user->organization,
                    'expertise' => $user->expertise,
                    'bio' => $user->bio,
                    'whatsapp_number' => $user->whatsapp_number,
                    'whatsapp_consent' => (bool) $user->whatsapp_consent,
                ] : null,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini salah.',
            ]);
        }

        $request->user()->update(['password' => $data['password']]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        if (!Hash::check($data['password'], $request->user()->password)) {
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }

        $request->user()->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }

    public function updateJuryProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isJury()) {
            abort(403, 'Fitur ini hanya untuk juri.');
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:40',
            'organization' => 'nullable|string|max:255',
            'expertise' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'whatsapp_number' => 'nullable|string|max:40',
            'whatsapp_consent' => 'boolean',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'Profil juri berhasil diperbarui.']);
    }
}