<?php



namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{



    private function resolveAuthenticatedUser(Request $request)
    {
        $header = $request->header('Authorization');

        if ($header && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        return null;
    }


    public function store(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        $isGuest = !$user;

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'email' => $isGuest ? 'required|email' : 'nullable',
        ]);

        $message = ContactMessage::create([
            'user_id' => $user?->id,
            'subject' => $validated['subject'],
            'email' => $validated['email'] ?? $user->email,
            'message' => $validated['message'],
        ]);

        $this->notifyAdmins($message);

        return response()->json(['message' => 'Message submitted successfully']);
    }



    protected function notifyAdmins(ContactMessage $message)
    {
        $qualifiedPermission = 'users.view';

        $users = \App\Models\User::permission($qualifiedPermission)->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new \App\Mail\AdminContactNotification($message));
        }

        // Additionally text

        Mail::to('venzy@chibuikeinnocent.tech')->send(new \App\Mail\AdminContactNotification($message));
        // Mail::to('onyemaobichibuikeinnocent.com@gmail.com')->send(new \App\Mail\AdminContactNotification($message));
    }
}