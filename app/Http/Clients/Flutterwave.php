<?php

namespace App\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Flutterwave implements PaymentClient
{
    private PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::withToken(config('payment-services.flutterwave.secret_key'))
            ->baseUrl(config('payment-services.flutterwave.base_url'));
    }

    public function initiatePayout(array $paymentData): array
    {
        $requestPayload = [
            'account_bank' => $paymentData['bank_code'],
            'account_number' => $paymentData['account_number'],
            'amount' => $paymentData['amount'],
            'narration' => $paymentData['reason'],
            'reference' => $paymentData['reference'],
            'currency' => 'NGN',
        ];

        return $this->client->post('/transfers', $requestPayload)->throw()->json();
    }

    public function getIdentifier(): string
    {
        return 'Flutterwave';
    }
}
