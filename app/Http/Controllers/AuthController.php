<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
                'message' => 'Email already exists'
            ], 422);
        }
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
    
            $token = $user->createToken('auth_token')->plainTextToken;
    
            // Response
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 403);    
        }
    }

    // Login
    public function login(Request $request){
        // Validate the request
        $validated = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        // Check if validation fails
        if ($validated->fails()){
            return response()->json(
                $validated->errors(),
                403
            );
        }

        $credentials = ['email' => $request->email,'password' => $request->password];
        try {  
          if(!auth()->attempt($credentials)){
            return response()->json([
                'message' => 'Email or password is incorrect'
            ], 401);
          }
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;
           
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([   
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();   

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
};
    