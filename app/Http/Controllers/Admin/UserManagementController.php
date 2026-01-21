<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('id', 'desc')->paginate(20);
        $roles = ['super_admin', 'admin', 'agent'];

        return view('sa.users.index', compact('users', 'roles'));
    }

    public function setRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:super_admin,admin,agent'],
        ]);

        // prevent removing your own super_admin accidentally
        if ($request->user()->id === $user->id && $request->role !== 'super_admin') {
            return back()->with('error', 'You cannot remove your own super_admin role.');
        }

        $user->syncRoles([$request->role]);

        return back()->with('success', 'Role updated successfully.');
    }
}
