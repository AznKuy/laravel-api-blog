<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function like(Post $post)
    {
        // Check if the user has already liked the post
        $isLiked = $post->likes()->where('user_id', Auth::id())->exists();

        if (!$isLiked) {
            $post->likes()->create([
                'user_id' => Auth::id(),
            ]);
        }

        // trigger notification
        /** @disregard */
        if ($post->user_id !== auth()->id()) {
            Notification::create([
                'user_id' => $post->user_id,
                'type' => 'like',
                'message' => auth()->user()->name . ' liked your post',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Post liked successfully',
        ], 200);
    }

    public function unlike(Post $post)
    {
        $post->likes()->where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post unliked successfully',
        ], 200);
    }
}
