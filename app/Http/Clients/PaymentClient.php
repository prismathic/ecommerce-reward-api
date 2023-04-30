<?php

namespace App\Http\Clients;

use App\Dtos\PaymentData;

abstract class PaymentClient
{
    public const BASE_CURRENCY = 'NGN';

    /**
     * Initiate a payout based on provided payment data.
     *
     * @param \App\Dtos\PaymentData $paymentData
     *
     * @return array
     */
    abstract public function initiatePayout(PaymentData $paymentData): array;

    /**
     * Get the unique identifier for the payment client.
     *
     * @return string
     */
    abstract public function getIdentifier(): string;
}
