<?php

namespace App\Services;

use App\Dtos\PaymentData;
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
    public function initiateCashbackPayment(Order $order, float $amount, string $reason): void
    {
        $payment = CashbackPayment::firstOrCreate([
            'order_id' => $order->id,
            'reason' => $reason,
        ], [
            'reference' => CashbackPayment::generateReference($order),
            'account_number' => $order->user->account_number,
            'bank_code' => $order->user->bank_code,
            'amount' => $amount,
            'payment_client' => $this->paymentClient->getIdentifier(),
            'status' => CashbackPayment::STATUSES['PENDING'],
        ]);

        if ($payment->status !== CashbackPayment::STATUSES['PENDING']) {
            return;
        }

        try {
            $payment->update(['status' => CashbackPayment::STATUSES['PROCESSING']]);

            $this->paymentClient->initiatePayout(PaymentData::from($payment));
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => CashbackPayment::STATUSES['FAILED']]);

            throw $e;
        }
    }
}
