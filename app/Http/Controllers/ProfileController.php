<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'user' => $request->user()->loadMissing('nominee'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, UserMediaService $media): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $media->deleteIfExists($user->image);
            $validated['image'] = $media->storeUserImage($request->file('image'), $user->id);
        } else {
            unset($validated['image']);
        }

        $nomineeInput = $validated['nominee'];
        unset($validated['nominee']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $nominee = $user->nominee()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $nomineeInput['name'],
                'email' => $nomineeInput['email'],
                'phone' => $nomineeInput['phone'],
                'nid_number' => $nomineeInput['nid_number'],
                'address' => $nomineeInput['address'],
            ]
        );

        if ($request->hasFile('nominee.image')) {
            $media->deleteIfExists($nominee->image);
            $nominee->update([
                'image' => $media->storeNomineeImage($request->file('nominee.image'), $user->id),
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
}
