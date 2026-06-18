<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('groups')->latest()->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $groups = Group::orderBy('name')->get();
        return view('users.create', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['groups'])) {
            $user->groups()->sync($validated['groups']);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $groups = Group::orderBy('name')->get();
        return view('users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'is_active' => 'boolean',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);

        if (isset($validated['groups'])) {
            $user->groups()->sync($validated['groups']);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }
}
