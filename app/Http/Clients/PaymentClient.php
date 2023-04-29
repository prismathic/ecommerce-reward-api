<?php

namespace App\Http\Clients;

interface PaymentClient
{
    /**
     * Initiate a payout based on provided payment data.
     *
     * @param array $paymentData
     *
     * @return array
     */
    public function initiatePayout(array $paymentData): array;

    /**
     * Get the unique identifier for the payment client.
     *
     * @return string
     */
    public function getIdentifier(): string;
}
