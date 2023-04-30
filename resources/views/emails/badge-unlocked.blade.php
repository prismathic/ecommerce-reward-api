<x-mail::message>
# Badge Unlocked!

<x-mail::panel>
Hey Superstar! <br>

You just unlocked a new badge: <strong>{{$badge}}</strong>!<br>

As a result of this great milestone, we will be paying you a cashback of {{$amount}} NGN on your recent order.
</x-mail::panel>

Thanks for shopping with us,<br>
{{ config('app.name') }}
</x-mail::message>
