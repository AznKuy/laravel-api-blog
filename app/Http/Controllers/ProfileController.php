<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    // show profile
    public function showProfile()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    // upate profile
    public function updateProfile(Request $request)
    {
        // dump($request->all());

        $user = auth()->user();

        $validated = Validator::make($request->all(), [
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bio' => 'required|string',
            'location' => 'required|string|max:255',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            $data = [];

            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $this->uploadImage($request->file('profile_photo'));
            }

            if ($request->filled('bio')) {
                $data['bio'] = $request->bio;
            }

            if ($request->filled('location')) {
                $data['location'] = $request->location;
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: '.$e->getMessage(),
            ], 500);
        }
    }

    // upload image
    public function uploadImage($profile_photo)
    {
        $fileName = time().'.'.Str::random(10).'.'.$profile_photo->getClientOriginalExtension();

        // save the image to the images folder
        $path = $profile_photo->storeAs('profile_photo', $fileName, 'public');

        return Storage::url($path);
    }
}
