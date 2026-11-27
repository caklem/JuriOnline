<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\JuryScore;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OwnerController extends Controller
{
    /**
     * Daftar seluruh pengguna beserta ringkasan akun.
     */
    public function users(Request $request): JsonResponse
    {
        $this->guardOwner($request);

        $search = trim((string) $request->string('q'));

        $users = User::with('role')
            ->when($search !== '', function ($query) use ($search) {
                $term = '%' . $search . '%';

                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->whereHas('role', function ($query) use ($request) {
                    $query->where('name', $request->input('role'));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => collect($users->items())->map(fn (User $user) => $this->serializeUser($user))->values(),
            'roles' => Role::orderBy('id')->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                ])->values(),
            'summary' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'owners' => User::where('is_active', true)
                    ->whereHas('role', fn ($query) => $query->where('name', Role::ADMIN_OWNER))
                    ->count(),
            ],
            'filters' => [
                'q' => $search,
                'role' => $request->input('role', ''),
                'status' => $request->input('status', ''),
            ],
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Tambah pengguna baru (langsung aktif).
     */
    public function storeUser(Request $request): JsonResponse
    {
        $this->guardOwner($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'event_scope' => ['nullable', 'in:all,own'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'event_scope.in' => 'Akses event tidak valid.',
        ], [
            'name' => 'nama',
            'role_id' => 'role',
            'event_scope' => 'akses event',
        ]);

        $validated['email'] = Str::lower($validated['email']);

        $role = Role::findOrFail($validated['role_id']);
        $validated['event_scope'] = $role->name === Role::ADMIN
            ? ($validated['event_scope'] ?? 'all')
            : 'all';

        $user = User::create($validated)->load('role');

        return response()->json([
            'message' => "Pengguna {$user->name} berhasil ditambahkan sebagai {$user->roleLabel()}.",
            'user' => $this->serializeUser($user),
        ], 201);
    }

    /**
     * Ubah role pengguna, dengan proteksi akun sendiri.
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        $this->guardOwner($request);

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'event_scope' => ['nullable', 'in:all,own'],
        ], [
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'event_scope.in' => 'Akses event tidak valid.',
        ]);

        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'role_id' => 'Anda tidak dapat mengubah role akun sendiri.',
            ]);
        }

        $role = Role::findOrFail($validated['role_id']);

        if ($user->role_id === $role->id) {
            throw ValidationException::withMessages([
                'role_id' => "{$user->name} sudah memiliki role {$role->display_name}.",
            ]);
        }

        $previous = $user->role?->display_name ?? 'tanpa role';
        $updates = ['role_id' => $role->id];

        if ($role->name === Role::ADMIN) {
            if ($request->filled('event_scope')) {
                $updates['event_scope'] = $validated['event_scope'];
            }
        } else {
            $updates['event_scope'] = 'all';
        }

        $user->update($updates);
        $user->load('role');

        return response()->json([
            'message' => "Role {$user->name} diubah dari {$previous} menjadi {$role->display_name}.",
            'user' => $this->serializeUser($user),
        ]);
    }

    /**
     * Atur cakupan event untuk akun Admin.
     */
    public function updateScope(Request $request, User $user): JsonResponse
    {
        $this->guardOwner($request);

        $validated = $request->validate([
            'event_scope' => ['required', 'in:all,own'],
        ], [
            'event_scope.required' => 'Akses event wajib dipilih.',
            'event_scope.in' => 'Akses event tidak valid.',
        ]);

        if (!$user->isAdmin()) {
            throw ValidationException::withMessages([
                'event_scope' => 'Akses event hanya dapat diatur untuk akun Admin.',
            ]);
        }

        $user->update(['event_scope' => $validated['event_scope']]);
        $user->load('role');

        $state = $validated['event_scope'] === 'own' ? 'hanya event sendiri' : 'semua event';

        return response()->json([
            'message' => "Akses event {$user->name} diubah menjadi {$state}.",
            'user' => $this->serializeUser($user),
        ]);
    }

    /**
     * Aktifkan / nonaktifkan akun, dengan proteksi akun sendiri.
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $this->guardOwner($request);

        if ($user->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'status' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        $user->update(['is_active' => !$user->isActive()]);
        $user->load('role');

        $state = $user->isActive() ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Akun {$user->name} berhasil {$state}.",
            'user' => $this->serializeUser($user),
        ]);
    }

    /**
     * Semua penilaian juri beserta statistik (owner / admin semua event).
     */
    public function scores(Request $request): JsonResponse
    {
        if (!$request->user()->canManageAllEvents()) {
            abort(403, 'Unauthorized action.');
        }

        $scores = JuryScore::with(['registration.event', 'registration.user', 'jury'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_scores' => JuryScore::count(),
            'total_events_scored' => Event::whereHas('registrations.juryScores')->count(),
            'average_score' => round((float) JuryScore::avg('score'), 2),
            'total_juries' => JuryScore::distinct('jury_id')->count('jury_id'),
        ];

        return response()->json([
            'data' => collect($scores->items())->map(function (JuryScore $score) {
                return [
                    'id' => $score->id,
                    'score' => (float) $score->score,
                    'public_feedback' => $score->public_feedback,
                    'private_evaluation' => $score->private_evaluation,
                    'created_at' => $score->created_at?->toIso8601String(),
                    'event' => [
                        'id' => $score->registration?->event?->id,
                        'name' => $score->registration?->event?->name,
                    ],
                    'participant' => [
                        'id' => $score->registration?->user?->id,
                        'name' => $score->registration?->user?->name,
                    ],
                    'jury' => [
                        'id' => $score->jury?->id,
                        'name' => $score->jury?->name,
                    ],
                ];
            })->values(),
            'stats' => $stats,
            'meta' => [
                'current_page' => $scores->currentPage(),
                'last_page' => $scores->lastPage(),
                'per_page' => $scores->perPage(),
                'total' => $scores->total(),
            ],
        ]);
    }

    private function guardOwner(Request $request): void
    {
        if (!$request->user()->isAdminOwner()) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'display_name' => $user->role->display_name,
            ] : null,
            'role_label' => $user->roleLabel(),
            'event_scope' => $user->event_scope,
            'is_active' => $user->isActive(),
            'is_admin' => $user->isAdmin(),
            'is_event_admin' => $user->isEventAdmin(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
