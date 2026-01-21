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
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->with(['roles', 'locations'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%"); // ✅ search phone too
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        $locations = Location::query()->orderBy('name')->get(['id', 'name']);

        $currentRole = old('role', $roles->first());
        $selectedLocations = old('locations', []);

        return view('admin.users.create', compact('roles', 'locations', 'currentRole', 'selectedLocations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],

            // ✅ phone: 9 digits, starts with 61
            'phone' => ['nullable', 'regex:/^61\d{7}$/', 'unique:users,phone'],

            'password' => ['required', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'status' => ['nullable', 'in:active,inactive'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', Rule::exists('locations', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,   // ✅ SAVE
                'password' => $validated['password'],
                'status' => $validated['status'] ?? 'active',
            ]);

            $user->syncRoles([$validated['role']]);

            $locationIds = $validated['locations'] ?? [];

            if ($validated['role'] === 'super_admin') {
                $locationIds = Location::query()->pluck('id')->toArray();
            }

            $user->locations()->sync($locationIds);
        });

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        $locations = Location::query()->orderBy('name')->get(['id', 'name']);

        $currentRole = $user->roles->pluck('name')->first();
        $selectedLocations = $user->locations()->pluck('locations.id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'locations', 'currentRole', 'selectedLocations'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],

            // ✅ phone unique ignore this user
            'phone' => ['nullable', 'regex:/^61\d{7}$/', Rule::unique('users', 'phone')->ignore($user->id)],

            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'status' => ['nullable', 'in:active,inactive'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', Rule::exists('locations', 'id')],
        ]);

        DB::transaction(function () use ($validated, $user) {

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'] ?? null; // ✅ UPDATE + SAVE

            if (!empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->status = $validated['status'] ?? ($user->status ?? 'active');
            $user->save();

            $user->syncRoles([$validated['role']]);

            $locationIds = $validated['locations'] ?? [];

            if ($validated['role'] === 'super_admin') {
                $locationIds = Location::query()->pluck('id')->toArray();
            }

            $user->locations()->sync($locationIds);
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

        DB::transaction(function () use ($user) {
            $user->locations()->detach();
            $user->syncRoles([]);
            $user->delete();
        });

        return back()->with('success', 'User deleted successfully.');
    }
}
