<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post) {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'comment' => $request->comment
        ]);

        $post->comments()->save($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'comment' => $comment
        ], 200);
    }
}
