<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        $frontendUrl = config('app.frontend_url', 'https://innoblog.com.ng');
        $url = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($this->email);
        
        return $this->view('emails.reset-password')
                    ->with([
                        'url' => $url,
                    ]);
    }
}