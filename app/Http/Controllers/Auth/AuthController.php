<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
// use Laravel\Sanctum\HasApiTokens;
// use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Automatically log the user in after registration
        Auth::login($user);

        // Create a new token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

         Mail::to($user->email)->send(new WelcomeEmail($user));

        return response()->json(['user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $user->load('roles', 'permissions');
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    public function logout(Request $request)
    {
        // Revoke all tokens for the user
        $request->user('sanctum')->tokens()->delete();
        // Logout the user from the web guard (session)
        Auth::guard('web')->logout();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}