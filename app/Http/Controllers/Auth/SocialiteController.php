<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to authenticate with Google.'], 401);
        }

        // Check if the user already exists in your database
        $user = User::where('email', $googleUser->email)->first();

        if ($user) {
            // If the user exists, log them in
            Auth::login($user);
        } else {
            // If the user doesn't exist, create a new user
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => bcrypt(str_random(16)) // You may set a random password since it's not used
            ]);

            Auth::login($user);
        }

        // Generate a token for the user
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // Return a JSON response with the token and user details
        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'Successfully logged in with Google!'
        ], 200);
    }
}
