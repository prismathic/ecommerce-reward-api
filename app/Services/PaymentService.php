<?php

namespace App\Services;

use App\Http\Clients\PaymentClient;
use App\Models\CashbackPayment;
use App\Models\Order;

class PaymentService
{
    public function __construct(private PaymentClient $paymentClient)
    {
    }

    /**
     * Process a cashback payment for an order.
     *
     * @param \App\Models\Order $order
     * @param float $amount
     * @param string $reason
     *
     * @return void
     */
    public function processCashback(Order $order, float $amount, string $reason): void
    {
        $requestPayload = [
            'account_number' => $order->user->account_number,
            'bank_code' => $order->user->bank_code,
            'reference' => $order->id,
            'reason' => $reason,
            'amount' => $amount,
        ];

        $this->paymentClient->initiatePayout($requestPayload);

        CashbackPayment::create([
            'order_id' => $order->id,
            'account_number' => $order->user->account_number,
            'bank_code' => $order->user->bank_code,
            'reason' => $reason,
            'amount' => $amount,
            'payment_client' => $this->paymentClient->getIdentifier(),
            'status' => CashbackPayment::STATUSES['PENDING'],
        ]);
    }
}
