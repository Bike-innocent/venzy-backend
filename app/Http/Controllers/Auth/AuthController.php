<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

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

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
        } catch (\Exception $e) {
            Log::error('Mail send failed: ' . $e->getMessage());
        }

        // Merge guest cart
        $this->mergeGuestCart($request);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'cart_count' => CartItem::where('user_id', $user->id)
                ->where('is_checked_out', false)
                ->sum('quantity'),
        ], 201);
    }










    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $user->load('roles');

        // Get flattened permissions (from roles + direct)
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Return only minimal role info (name)
        $roles = $user->roles->pluck('name')->toArray();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Merge guest cart logic
        $this->mergeGuestCart($request);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dial_code' => $user->dial_code,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            'cart_count' => CartItem::where('user_id', $user->id)
                ->where('is_checked_out', false)
                ->sum('quantity'),
        ], 201);
    }


    protected function mergeGuestCart(Request $request)
    {
        $user = $request->user();
        if (!$user) return;

        $guestId = $request->header('X-Guest-Id');
        if (!$guestId) return;

        $guestItems = CartItem::where('guest_id', $guestId)
            ->where('is_checked_out', false)
            ->get();

        foreach ($guestItems as $item) {
            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('is_checked_out', false)
                ->first();

            if ($existing) {
                $existing->quantity += $item->quantity;
                $existing->save();
                $item->delete();
            } else {
                $item->user_id = $user->id;
                $item->guest_id = null;
                $item->save();
            }
        }
    }







    public function refreshUser(Request $request)
    {
        $user = $request->user()->load('roles');
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->roles->pluck('name')->toArray();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $roles,
            'phone' => $user->phone,
            'dial_code' => $user->dial_code,
            'permissions' => $permissions,
        ]);
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