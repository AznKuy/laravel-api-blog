<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // get all posts
    public function index(Request $request)
    {
        $query = Post::with(['user', 'category']);


        // filter posts by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // filter posts by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // filter posts by user
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        // filter by newest or oldest
        if ($request->filled('sort') && $request->sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $posts = $query->paginate(10);

        // if posts not found
        if ($posts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No posts found',
            ], 404);
        }


        return new PostCollection($posts);
    }

    // get a single post
    public function show(Post $post)
    {
        $post->load(['user', 'category']);
        return new PostResource($post);
    }

    // Create a new post
    public function store(Request $request)
    {
        // Validate the request
        $validated = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $imagePath = $this->uploadImage($request->file('image'));

            $post = Post::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
                'image' => $imagePath,
                'category_id' => $request->category_id,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'post' => new PostResource($post->load(['user', 'category'])),
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            // delete the uploaded image if the post creation fails
            if (isset($imagePath)) {
                $this->deleteImage($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update a post
    public function update(Request $request, Post $post)
    {

        // dd($request->all());

        // Validate the request
        $validated = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:posts,title,' . $post->id,
            'content' => 'required|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // update the post
            $post->update([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
                'category_id' => $request->category_id,
            ]);

            if ($request->hasFile('image')) {
                // delete the old image if a new image is uploaded
                $this->deleteImage($post->image);

                // upload the new image
                $post->image = $this->uploadImage($request->file('image'));
                $post->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'post' => new PostResource($post->load(['user', 'category'])),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // delete the uploaded image if the post creation fails
            if (isset($post['image'])) {
                $this->deleteImage($post['image']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update post: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete a post
    public function destroy(Post $post)
    {

        try {
            DB::beginTransaction();

            $this->deleteImage($post->image);
            $post->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post: ' . $e->getMessage()
            ], 500);
        }
    }


    protected function uploadImage($image)
    {
        $fileName = time() . '.' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        // save the image to the images folder
        $path = $image->storeAs('images', $fileName, 'public');

        return Storage::url($path);
    }

    protected function deleteImage($path)
    {
        $relativePath = str_replace('storage/', '', $path);
        Storage::disk('public')->delete($relativePath);
    }
}
