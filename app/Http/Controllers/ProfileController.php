<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['skills'])) {
            // Convert comma-separated string to an array and trim whitespace
            $data['skills'] = array_map('trim', explode(',', $data['skills']));
            // Remove empty values
            $data['skills'] = array_filter($data['skills']);
        } else {
            $data['skills'] = [];
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()->password) {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        }

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update user's notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.mail' => 'nullable|boolean',
            'preferences.*.database' => 'nullable|boolean',
        ]);

        $sanitized = [];
        $types = ['task_assigned', 'comment_added', 'discussion_replied', 'member_added'];

        foreach ($types as $type) {
            $sanitized[$type] = [
                'mail' => (bool) ($validated['preferences'][$type]['mail'] ?? false),
                'database' => (bool) ($validated['preferences'][$type]['database'] ?? false),
            ];
        }

        $request->user()->update([
            'notification_preferences' => $sanitized,
        ]);

        return Redirect::route('profile.edit')->with('status', 'notifications-updated');
    }
}
