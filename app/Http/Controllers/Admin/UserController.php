<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function allowedRolesForCreator(User $creator): array
    {
        // Default rules you chose:
        // Admin -> only agent
        // Super Admin -> admin + agent
        return $creator->hasRole('super_admin')
            ? ['admin', 'agent']
            : ['agent'];
    }

    private function allowedLocationIdsForCreator(User $creator): array
    {
        // Super Admin can assign any location
        if ($creator->hasRole('super_admin')) {
            return Location::query()->pluck('id')->all();
        }

        // Admin can assign only their own locations
        return $creator->locations()->pluck('locations.id')->all();
    }

    public function index()
    {
        $users = User::query()
            ->with(['roles', 'locations'])
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $creator = auth()->user();

        $roles = $this->allowedRolesForCreator($creator);

        $locations = Location::query()
            ->when(!$creator->hasRole('super_admin'), function ($q) use ($creator) {
                $q->whereIn('id', $creator->locations()->pluck('locations.id'));
            })
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('roles', 'locations'));
    }

    public function store(Request $request)
    {
        $creator = auth()->user();

        $allowedRoles = $this->allowedRolesForCreator($creator);
        $allowedLocationIds = $this->allowedLocationIdsForCreator($creator);

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'locations' => ['required','array','min:1'],
            'locations.*' => ['integer', Rule::in($allowedLocationIds)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->syncRoles([$data['role']]);
        $user->locations()->sync($data['locations']);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $creator = auth()->user();

        // Security: admin cannot edit super_admin accounts
        if (!$creator->hasRole('super_admin') && $user->hasRole('super_admin')) {
            abort(403);
        }

        $roles = $this->allowedRolesForCreator($creator);

        $locations = Location::query()
            ->when(!$creator->hasRole('super_admin'), function ($q) use ($creator) {
                $q->whereIn('id', $creator->locations()->pluck('locations.id'));
            })
            ->orderBy('name')
            ->get();

        $currentRole = $user->roles->first()?->name;
        $selectedLocations = $user->locations()->pluck('locations.id')->all();

        return view('admin.users.edit', compact('user','roles','locations','currentRole','selectedLocations'));
    }

    public function update(Request $request, User $user)
    {
        $creator = auth()->user();

        if (!$creator->hasRole('super_admin') && $user->hasRole('super_admin')) {
            abort(403);
        }

        $allowedRoles = $this->allowedRolesForCreator($creator);
        $allowedLocationIds = $this->allowedLocationIdsForCreator($creator);

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'locations' => ['required','array','min:1'],
            'locations.*' => ['integer', Rule::in($allowedLocationIds)],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        $user->syncRoles([$data['role']]);
        $user->locations()->sync($data['locations']);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $creator = auth()->user();

        // block deleting super_admin unless super_admin
        if (!$creator->hasRole('super_admin') && $user->hasRole('super_admin')) {
            abort(403);
        }

        // optional: prevent self-delete
        if ($creator->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
