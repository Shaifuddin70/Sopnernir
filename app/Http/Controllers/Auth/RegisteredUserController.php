<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\UserMediaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterUserRequest $request, UserMediaService $media): RedirectResponse
    {
        $validated = $request->validated();
        $nomineeInput = $validated['nominee'];
        unset($validated['nominee'], $validated['password_confirmation'], $validated['image']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = User::ROLE_INVESTOR;

        $user = DB::transaction(function () use ($request, $media, $validated, $nomineeInput) {
            $user = User::create($validated);

            if ($request->hasFile('image')) {
                $user->update([
                    'image' => $media->storeUserImage($request->file('image'), $user->id),
                ]);
            }

            $nominee = $user->nominee()->create([
                'name' => $nomineeInput['name'],
                'email' => $nomineeInput['email'],
                'phone' => $nomineeInput['phone'],
                'nid_number' => $nomineeInput['nid_number'],
                'address' => $nomineeInput['address'],
            ]);

            if ($request->hasFile('nominee.image')) {
                $nominee->update([
                    'image' => $media->storeNomineeImage($request->file('nominee.image'), $user->id),
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
