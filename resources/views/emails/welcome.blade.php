@component('mail::message')
# Welcome {{ $user->name }} 👋

Thank you for registering with us!

@component('mail::button', ['url' => url('/')])
Visit Our Website
@endcomponent

We’re glad to have you onboard.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
