@component('mail::message')
# Reset Your Password

Hello,

We received a request to reset the password for your account.

Click the button below to reset your password:

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
