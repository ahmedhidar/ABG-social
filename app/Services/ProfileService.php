<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $profilePicture = null): User
    {
        if ($profilePicture) {
            // Delete old picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $profilePicture->store('profiles', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return $user;
    }
}
