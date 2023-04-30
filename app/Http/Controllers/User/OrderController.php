<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Process an order request.
     *
     * @param \App\Http\Requests\CreateOrderRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CreateOrderRequest $request): JsonResponse
    {
        //validate user's payment information, check that they can make payment etc
        $user = auth()->user();
        $product = Product::find($request->product);

        try {
            $order = $user->orders()->create([
                'status' => Order::STATUS_PENDING,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'total' => $product->price * $request->quantity,
            ]);

            ProcessOrder::dispatch($order);

            return $this->okResponse(
                'Your order has started processing, you will be notified when it is completed.',
                ['order_id' => $order->id]
            );
        } catch (\Throwable $th) {
            report($th);

            return $this->serverErrorResponse('An error occurred while attempting to process your order.');
        }
    }
}
