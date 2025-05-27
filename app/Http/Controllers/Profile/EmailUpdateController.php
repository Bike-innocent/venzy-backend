<?php


namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\EmailChangeVerificationMail;
use App\Models\User;

class EmailUpdateController extends Controller
{
    /**
     * Request to change the user's email address.
     * Sends a verification link to the new email.
     */
    public function requestChange(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $user = Auth::user();

        $token = Str::random(64);

        // Remove any existing pending email change requests
        DB::table('pending_user_emails')->where('user_id', $user->id)->delete();

        DB::table('pending_user_emails')->insert([
            'user_id' => $user->id,
            'new_email' => $request->email,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send email to the new address with verification link
        Mail::to($request->email)->send(new EmailChangeVerificationMail($token, $user));

        return response()->json(['message' => 'A verification link has been sent to your new email address.']);
    }

    /**
     * Verify the token and change the user's email if valid.
     */
    // public function verifyChange($token)
    // {
    //     $pending = DB::table('pending_user_emails')->where('token', $token)->first();

    //     if (!$pending || now()->greaterThan($pending->expires_at)) {
    //         return response()->json(['message' => 'Invalid or expired verification token.'], 400);
    //     }

    //     $user = User::find($pending->user_id);
    //     if (!$user) {
    //         return response()->json(['message' => 'User not found.'], 404);
    //     }

    //     $user->email = $pending->new_email;
    //     $user->email_verified_at = now(); // optional: re-verify the email
    //     $user->save();

    //     // Clean up pending request
    //     DB::table('pending_user_emails')->where('id', $pending->id)->delete();

    //     return response()->json(['message' => 'Email updated successfully.']);
    // }


    public function verifyChange($token)
    {
        $pending = DB::table('pending_user_emails')->where('token', $token)->first();

        if (!$pending || now()->greaterThan($pending->expires_at)) {
            return redirect(config('app.frontend_url') . '/account/profile?status=error&message=Invalid or expired verification token.');
        }

        $user = User::find($pending->user_id);
        if (!$user) {
            return redirect(config('app.frontend_url') . '/account/profile?status=error&message=User not found.');
        }

        $user->email = $pending->new_email;
        $user->email_verified_at = now();
        $user->save();

        DB::table('pending_user_emails')->where('id', $pending->id)->delete();

        return redirect(config('app.frontend_url') . '/account/profile?status=success&message=Email updated successfully.');
    }
}