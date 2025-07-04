@component('mail::message')
# New Contact Message

**From:** {{ $messageData->email ?? $messageData->user->email }}

**Subject:** {{ $messageData->subject }}

**Message:**

{{ $messageData->message }}
{{-- 
Thanks,<br>
{{ config('app.name') }} --}}
@endcomponent
