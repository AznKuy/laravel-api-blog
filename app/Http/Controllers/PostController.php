<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // get all posts
    public function index()
    {
        $posts = Post::with(['user', 'category'])
            ->latest()
            ->paginate(10);

        return new PostCollection($posts);
    }

    // get a single post
    public function show($id)
    {
        $post = Post::with(['user', 'category'])->findOrFail($id);
        return new PostResource($post);
    }

    // Create a new post
    public function create(Request $request)
    {
        // Validate the request
        $validated = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        try {
            // image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request->file('image'));
            }

            $post = new Post();
            $post->title = $request->title;
            $post->slug = Str::slug($request->title);
            $post->content = $request->content;
            $post->image = $imagePath ?? null; // Store the image path
            $post->category_id = $request->category_id;
            $post->user_id = auth()->user()->id;
            $post->save();

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Post created successfully',
                'post' => $post,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadImage($image)
    {
        $fileName = time() . '.' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        // save the image to the images folder
        $path = $image->storeAs('images', $fileName, 'public');

        return Storage::url($path);
    }
}
