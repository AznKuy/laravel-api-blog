<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="My API Documentation",
 *     description="Documentation for my API",
 * )
 */

class PostController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Post",
     *     type="object",
     *     title="Post",
     *     required={"id", "title", "content", "image", "category_id", "user_id"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="title", type="string", example="Judul Postingan"),
     *     @OA\Property(property="slug", type="string", example="judul-postingan"),
     *     @OA\Property(property="content", type="string", example="Isi dari post ini..."),
     *     @OA\Property(property="image", type="string", example="https://url-image.com/image.jpg"),
     *     @OA\Property(property="category_id", type="integer", example=2),
     *     @OA\Property(property="user_id", type="integer", example=5),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/api/posts/{post}",
     *  summary="Get a single post",
     *  tags={"Post"},
     *  @OA\Parameter(
     *      name="post",
     *      in="path",
     *      description="Post ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *     @OA\Response(
     *         response=200,
     *         description="Single post",
     *         @OA\JsonContent(
     *             @OA\Property(property="data",  type = "array", @OA\Items(ref="#/components/schemas/Post")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(    
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    // get a single post
    public function show(Post $post)
    {
        $post->load(['user', 'category']);

        return new PostResource($post);
    }


    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create a new post",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "image", "category_id"},
     *                 @OA\Property(property="title", type="string", example="My Awesome Blog Post"),
     *                 @OA\Property(property="content", type="string", example="This is the content of the post."),
     *                 @OA\Property(property="image", type="file", format="binary"),
     *                 @OA\Property(property="category_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post created successfully"),
     *             @OA\Property(property="post", ref="#/components/schemas/Post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create post"
     *     )
     * )
     */

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

            /**  @disregard  */
            $post = Post::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
                'image' => $imagePath,
                'category_id' => $request->category_id,
                'user_id' => auth()->id(),
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


    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     summary="Update an existing post",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "category_id"},
     *                 @OA\Property(property="title", type="string", example="Updated Title"),
     *                 @OA\Property(property="content", type="string", example="Updated content."),
     *                 @OA\Property(property="image", type="file", format="binary"),
     *                 @OA\Property(property="category_id", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update post"
     *     )
     * )
     */

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
                'message' => 'Failed to update post: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Delete a post",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete post"
     *     )
     * )
     */

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
                'message' => 'Failed to delete post: ' . $e->getMessage(),
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
