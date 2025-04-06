<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function index(Post $post)
    {
        $comments = $post->comments()->with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'comments' => CommentResource::collection($comments)
        ], 200);
    }
    public function store(Request $request, Post $post)
    {
        $validated = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        try {

            $comment = Comment::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'comment' => $request->comment
            ]);

            logger('Created comment', $comment->toArray()); // <- tambahkan ini

            return response()->json([
                'success' => true,
                'message' => 'Comment created successfully',
                'comment' => new CommentResource($comment)
            ], 201);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update Comment
    public function update(Request $request, Comment $comment)
    {

        $validated = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors()
            ], 422);
        }

        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $comment->update([
                'comment' => $request->comment
            ]);

            $comment = Comment::with('user')->find($comment->id);
            // @dump($comment);

            return response()->json([
                'success' => 'true',
                'message' => 'Comment Updated Successfully',
                'comment' => new CommentResource($comment)
            ], 200);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a Comment
    public function destroy(Comment $comment)
    {

        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted Successfully'
            ]);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'Failed deleted Successfully',
                'erros' => $e->getMessage()
            ], 500);
        }
    }
}
