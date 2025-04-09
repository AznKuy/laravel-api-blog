<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * OA\Tag(
 *     name="Auth Endpoints",
 *     description="Authentication endpoints",
 * )
 */

class AuthController extends Controller
{

    /**
     * OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *          request="RegisterRequest",
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string",format="emial", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"), 
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"), 
     * )),  
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Validation Failed",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Email already exists",
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error"),
     *         )
     *     )
     * )
     */
    // Register
    public function register(Request $request)
    {
        // Validate the request
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        // Check if validation fails
        if ($validated->fails()) {
            return response()->json(
                $validated->errors(),
                403
            );
        }
        // Check if the email already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'Email already exists',
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Response
            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"), 
     * )),  
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Validation Failed",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Email or password is incorrect",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error"),
     *         )
     *     )
     * )
     */

    // Login
    public function login(Request $request)
    {
        // Validate the request
        $validated = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        // Check if validation fails
        if ($validated->fails()) {
            return response()->json(
                $validated->errors(),
                403
            );
        }

        $credentials = ['email' => $request->email, 'password' => $request->password];
        try {
            if (! Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Email or password is incorrect',
                ], 401);
            }
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 403);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout a user",
     *     description="Logout a user",
     *     tags={"Auth"},
     *      security={{"sanctum": {} } },
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error"),
     *         )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}
