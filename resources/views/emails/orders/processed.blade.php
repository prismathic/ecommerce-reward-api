<x-mail::message>
# Order Processed!
 
Your order of ID: {{$order->id}} has successfully been processed.
 
<x-mail::button :url="$url">
View Order
</x-mail::button>
 
Thanks for shopping with us,<br>
{{ config('app.name') }}
</x-mail::message>
