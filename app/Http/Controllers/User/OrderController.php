<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Jobs\ProcessOrder;
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

        ProcessOrder::dispatch(auth()->user(), $request->validated());

        return $this->okResponse('Your order has started processing, you will be notified when it is completed.');
    }
}
