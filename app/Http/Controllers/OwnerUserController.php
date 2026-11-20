<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OwnerUserController extends Controller
{
    public function index(Request $request)
    {
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

        $roles = Role::orderBy('id')->get();

        $summary = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'owners' => User::where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', Role::ADMIN_OWNER))
                ->count(),
        ];

        return view('owner.users', compact('users', 'roles', 'summary', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'event_scope' => ['nullable', 'in:all,own'],
        ], [], [
            'name' => 'nama',
            'role_id' => 'role',
            'event_scope' => 'akses event',
        ]);

        $validated['email'] = Str::lower($validated['email']);

        $role = Role::findOrFail($validated['role_id']);
        $validated['event_scope'] = $role->name === Role::ADMIN
            ? ($validated['event_scope'] ?? 'all')
            : 'all';

        $user = User::create($validated);

        return redirect()->route('owner.users.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan sebagai {$user->roleLabel()}.");
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'event_scope' => ['nullable', 'in:all,own'],
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
        }

        $role = Role::findOrFail($validated['role_id']);

        if ($user->role_id === $role->id) {
            return back()->with('error', "{$user->name} sudah memiliki role {$role->display_name}.");
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

        return back()->with('success', "Role {$user->name} diubah dari {$previous} menjadi {$role->display_name}.");
    }

    public function updateScope(Request $request, User $user)
    {
        $validated = $request->validate([
            'event_scope' => ['required', 'in:all,own'],
        ]);

        if (!$user->isAdmin()) {
            return back()->with('error', 'Akses event hanya dapat diatur untuk akun Admin.');
        }

        $user->update(['event_scope' => $validated['event_scope']]);

        $state = $validated['event_scope'] === 'own' ? 'hanya event sendiri' : 'semua event';

        return back()->with('success', "Akses event {$user->name} diubah menjadi {$state}.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->isActive()]);

        $state = $user->isActive() ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$state}.");
    }
}
