@component('mail::message')
# Hello {{ $name }},

We received a request to change the email address associated with your account.

Please click the button below to confirm your new email address:

@component('mail::button', ['url' => $url])
Confirm Email Address
@endcomponent

If you didn’t request this change, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
