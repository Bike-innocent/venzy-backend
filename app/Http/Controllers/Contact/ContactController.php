<?php

// namespace App\Http\Controllers\Contact;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;
// use App\Models\ContactMessage; // Create this model for storing messages in the database

// class ContactController extends Controller
// {
//     public function sendContactMessage(Request $request)
//     {
//         // Validate the incoming request
//         $validated = $request->validate([
//             'email' => 'required|email',
//             'message' => 'required|string|min:5',
//         ]);

//         // Save message to database (optional)
//         $message = ContactMessage::create([
//             'email' => $validated['email'],
//             'message' => $validated['message'],
//         ]);

//         // Send an email to yourself
//         Mail::send([], [], function ($mail) use ($validated) {
//             $mail->to(['hello@buike.com.ng', 'onyemaobichibuikeinnocent.com@gmail.com']) // Multiple emails
//                 ->subject('New Contact Message')
//                 ->setBody(null, 'text/plain') // Set the type to plain text
//                 ->text('You have received a new message from ' . $validated['email'] . ' with the message: ' . $validated['message']);
//         });
        

//         return response()->json(['message' => 'Message sent successfully!'], 200);
//     }
// }






namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function sendContactMessage(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'email' => 'required|email',
            'message' => 'required|string|min:5',
        ]);

        // Save message to database (optional)
        ContactMessage::create([
            'email' => $validated['email'],
            'message' => $validated['message'],
        ]);

        // Send an email to yourself (plain text)
        Mail::raw(
            'You have received a new message from ' . $validated['email'] . ' with the message: ' . $validated['message'],
            function ($mail) use ($validated) {
                $mail->to(['hello@chibuikeinnocent.tech', 'onyemaobichibuikeinnocent.com@gmail.com'])
                    ->subject('New Contact Message');
            }
        );

        // Return success response
        return response()->json(['message' => 'Message sent successfully!'], 200);
    }
}






















// ok nice i want us to handle the static frontend pages logic with  backend here is it Contact Messages,Faq so we will start with contact messages so guest or customer can message us if is guest it will require subject,email and message if is user or customer it will require subject and message so lets thesign the backend first and also we can add an email logic to send message to admin or user permitted roles so lets do it well then after will can display it on frontend too