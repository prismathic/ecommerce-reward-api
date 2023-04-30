<x-mail::message>
# Order Failed!
 
We regret to inform you that your order of ID: {{$order->id}} failed while processing. <br>
We know this wasn't the experience you were expecting, we will keep providing you with updates on the status of your order.
 
<x-mail::button :url="$url">
View Order
</x-mail::button>
 
Thanks for shopping with us,<br>
{{ config('app.name') }}
</x-mail::message>
