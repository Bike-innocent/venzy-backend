


@component('mail::message')
# Welcome to Venzy, {{ $user->name }}! 🛍️

Thank you for registering with us!

At **Venzy**, you’ll discover the latest perfumes and men’s wear  to match your style and budget.

@component('mail::button', ['url' => 'https://venzy.vercel.app/shop'])
Start Shopping
@endcomponent

We’re excited to have you on board and can’t wait for you to explore what we have in store.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
