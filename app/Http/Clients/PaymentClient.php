<?php

namespace App\Http\Clients;

use App\Dtos\PaymentData;

interface PaymentClient
{
    /**
     * Initiate a payout based on provided payment data.
     *
     * @param \App\Dtos\PaymentData $paymentData
     *
     * @return array
     */
    public function initiatePayout(PaymentData $paymentData): array;

    /**
     * Get the unique identifier for the payment client.
     *
     * @return string
     */
    public function getIdentifier(): string;
}
