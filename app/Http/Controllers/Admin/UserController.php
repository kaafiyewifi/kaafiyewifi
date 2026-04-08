<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private function isSuperAdmin(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('super_admin');
    }

    private function isAdmin(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('admin');
    }

    private function allowedRoleNamesForCurrentUser(): array
    {
        if ($this->isSuperAdmin()) {
            return ['super_admin', 'admin', 'support'];
        }

        if ($this->isAdmin()) {
            return ['support'];
        }

        return [];
    }

    private function allowedLocationIdsForCurrentUser(): array
    {
        if ($this->isSuperAdmin()) {
            return Location::query()->pluck('id')->toArray();
        }

        return auth()->user()
            ? auth()->user()->locations()->pluck('locations.id')->toArray()
            : [];
    }

    private function canAccessUserByLocation(User $user): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $viewerLocationIds = $this->allowedLocationIdsForCurrentUser();
        $userLocationIds = $user->locations()->pluck('locations.id')->toArray();

        return !empty(array_intersect($viewerLocationIds, $userLocationIds));
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $viewer = auth()->user();

        $query = User::query()->with(['roles', 'locations']);

        if (!$this->isSuperAdmin()) {
            $locationIds = $viewer->locations()->pluck('locations.id')->toArray();

            $query->whereHas('locations', function ($q) use ($locationIds) {
                $q->whereIn('locations.id', $locationIds);
            });

            // ✅ non-super-admin ma arki karo super_admin
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            });

            // ✅ non-super-admin wuxuu arkaa admin + support kaliya
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'support']);
            });
        }

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $users = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        $allowedRoles = $this->allowedRoleNamesForCurrentUser();
        abort_if(empty($allowedRoles), 403);

        $roles = Role::query()
            ->whereIn('name', $allowedRoles)
            ->orderByRaw("FIELD(name, 'super_admin', 'admin', 'support')")
            ->pluck('name');

        $locationIds = $this->allowedLocationIdsForCurrentUser();

        $locations = Location::query()
            ->whereIn('id', $locationIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $currentRole = old('role', $roles->first());
        $selectedLocations = old('locations', []);

        if ($this->isAdmin() && empty($selectedLocations)) {
            $selectedLocations = $locationIds;
        }

        return view('admin.users.create', compact('roles', 'locations', 'currentRole', 'selectedLocations'));
    }

    public function store(Request $request)
    {
        $allowedRoles = $this->allowedRoleNamesForCurrentUser();
        abort_if(empty($allowedRoles), 403);

        $allowedLocationIds = $this->allowedLocationIdsForCurrentUser();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'regex:/^61\d{7}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'status' => ['nullable', 'in:active,inactive'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', Rule::exists('locations', 'id')],
        ]);

        $requestedLocationIds = array_map('intval', $validated['locations'] ?? []);

        if ($this->isAdmin()) {
            $locationIds = $allowedLocationIds;
        } else {
            $locationIds = array_values(array_intersect($requestedLocationIds, $allowedLocationIds));
        }

        DB::transaction(function () use ($validated, $locationIds) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'status' => $validated['status'] ?? 'active',
            ]);

            $user->syncRoles([$validated['role']]);

            $finalLocationIds = $locationIds;

            if ($validated['role'] === 'super_admin' && $this->isSuperAdmin()) {
                $finalLocationIds = Location::query()->pluck('id')->toArray();
            }

            $user->locations()->sync($finalLocationIds);
        });

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        if (!$this->isSuperAdmin() && $user->hasRole('super_admin')) {
            abort(403);
        }

        if (!$this->canAccessUserByLocation($user)) {
            abort(403);
        }

        $allowedRoles = $this->allowedRoleNamesForCurrentUser();
        abort_if(empty($allowedRoles), 403);

        $roles = Role::query()
            ->whereIn('name', $allowedRoles)
            ->orderByRaw("FIELD(name, 'super_admin', 'admin', 'support')")
            ->pluck('name');

        $locationIds = $this->allowedLocationIdsForCurrentUser();

        $locations = Location::query()
            ->whereIn('id', $locationIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $currentRole = $user->roles->pluck('name')->first();
        $selectedLocations = $user->locations()->pluck('locations.id')->toArray();

        if ($this->isAdmin()) {
            $selectedLocations = $locationIds;
        }

        return view('admin.users.edit', compact('user', 'roles', 'locations', 'currentRole', 'selectedLocations'));
    }

    public function update(Request $request, User $user)
    {
        if (!$this->isSuperAdmin() && $user->hasRole('super_admin')) {
            abort(403);
        }

        if (!$this->canAccessUserByLocation($user)) {
            abort(403);
        }

        $allowedRoles = $this->allowedRoleNamesForCurrentUser();
        abort_if(empty($allowedRoles), 403);

        $allowedLocationIds = $this->allowedLocationIdsForCurrentUser();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/^61\d{7}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'status' => ['nullable', 'in:active,inactive'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', Rule::exists('locations', 'id')],
        ]);

        $requestedLocationIds = array_map('intval', $validated['locations'] ?? []);

        if ($this->isAdmin()) {
            $locationIds = $allowedLocationIds;
        } else {
            $locationIds = array_values(array_intersect($requestedLocationIds, $allowedLocationIds));
        }

        DB::transaction(function () use ($validated, $user, $locationIds) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'] ?? null;

            if (!empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->status = $validated['status'] ?? ($user->status ?? 'active');
            $user->save();

            $user->syncRoles([$validated['role']]);

            $finalLocationIds = $locationIds;

            if ($validated['role'] === 'super_admin' && $this->isSuperAdmin()) {
                $finalLocationIds = Location::query()->pluck('id')->toArray();
            }

            $user->locations()->sync($finalLocationIds);
        });

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->hasRole('super_admin')) {
            return back()->with('error', 'You cannot delete super admin.');
        }

        if (!$this->canAccessUserByLocation($user)) {
            abort(403);
        }

        if ($this->isAdmin() && !$user->hasRole('support')) {
            return back()->with('error', 'Admin can delete support only.');
        }

        DB::transaction(function () use ($user) {
            $user->locations()->detach();
            $user->syncRoles([]);
            $user->delete();
        });

        return back()->with('success', 'User deleted successfully.');
    }
}