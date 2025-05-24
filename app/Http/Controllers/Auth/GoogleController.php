<?php


namespace App\Http\Controllers\Auth;
// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class GoogleController extends Controller
{
    // Redirect to Google login
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

//     public function handleGoogleCallback()
// {
//     try {
//         $googleUser = Socialite::driver('google')->stateless()->user();

//         // Find or create the user
//         $user = User::updateOrCreate(
//             ['email' => $googleUser->email],
//             [
//                 'name' => $googleUser->name,
//                 'google_id' => $googleUser->id,
//                 'password' => Hash::make(uniqid()), // Generate random password
//             ]
//         );

//         // Log the user in
//         Auth::login($user);

//         // Generate a token (if using Sanctum)
//         $token = $user->createToken('authToken')->plainTextToken;

//         // Redirect to the frontend with the token
//         return redirect()->away("https://innoblog.com.ng?token={$token}");

//     } catch (\Exception $e) {
//         return response()->json(['error' => 'Failed to authenticate', 'message' => $e->getMessage()], 500);
//     }
// }


public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Find or create the user
        $user = User::updateOrCreate(
            ['email' => $googleUser->email],
            [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'password' => Hash::make(uniqid()), // Generate random password
            ]
        );

        // Log the user in
        Auth::login($user);

        // Generate a token (if using Sanctum)
        $token = $user->createToken('authToken')->plainTextToken;

        // Redirect to the frontend with the auth token identified by 'authToken'
        return redirect()->away("https://innoblog.com.ng?authToken={$token}");

    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to authenticate', 'message' => $e->getMessage()], 500);
    }
}



}
