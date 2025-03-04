<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        return Inertia::render('Profile/Edit', [
            'profile' => auth()->user()->profile
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'bio' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'notifications_enabled' => ['boolean'],
            'public_profile' => ['boolean'],
            'theme' => ['string', 'in:dark,light,system'],
        ]);

        $user = $request->user();
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->profile->update([
            'bio' => $validated['bio'],
            'website' => $validated['website'],
            'notifications_enabled' => $validated['notifications_enabled'],
            'public_profile' => $validated['public_profile'],
            'theme' => $validated['theme'],
        ]);

        return back()->with('message', 'Профиль обновлен');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:1024'],
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');
        
        if ($request->user()->profile->avatar) {
            Storage::disk('public')->delete($request->user()->profile->avatar);
        }

        $request->user()->profile->update([
            'avatar' => $path,
        ]);

        return back()->with('message', 'Аватар обновлен');
    }
} 