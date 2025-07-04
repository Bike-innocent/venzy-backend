<?php



namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $isGuest = auth()->guest();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'email' => $isGuest ? 'required|email' : 'nullable',
        ]);

        $message = ContactMessage::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'email' => $validated['email'] ?? auth()->user()->email,
            'message' => $validated['message'],
        ]);

        // Send email to admins (optional)
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

        // Additionally notify a specific fallback/override email
        Mail::to('onyemaobichibuikeinnocent.com@gmail.com')->send(new \App\Mail\AdminContactNotification($message));
    }
}