<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\FriendRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected ProfileService $profileService)
    {
    }

    public function show(User $user): View
    {
        $posts = $user->posts()->latest()->paginate(10);
        $auth = auth()->user();

        // Relationship status checks
        $sentRequest = FriendRequest::where('sender_id', $auth->id)
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        $receivedRequest = FriendRequest::where('sender_id', $user->id)
            ->where('receiver_id', $auth->id)
            ->where('status', 'pending')
            ->first();

        $isFriend = $auth->friends()->where('friend_id', $user->id)->exists();

        return view('profile.show', compact('user', 'posts', 'sentRequest', 'receivedRequest', 'isFriend'));
    }

    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileService->updateProfile(
            auth()->user(),
            $request->validated(),
            $request->file('profile_picture')
        );

        return back()->with('success', 'Profile updated successfully!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
