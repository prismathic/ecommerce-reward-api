<?php

namespace App\Http\Clients;

use App\Dtos\PaymentData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Flutterwave extends PaymentClient
{
    private PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::withToken(config('payment-services.flutterwave.secret_key'))
            ->baseUrl(config('payment-services.flutterwave.base_url'));
    }

    public function initiatePayout(PaymentData $paymentData): array
    {
        $requestPayload = [
            'account_bank' => $paymentData->bank_code,
            'account_number' => $paymentData->account_number,
            'amount' => $paymentData->amount,
            'narration' => $paymentData->reason,
            'reference' => $paymentData->reference,
            'currency' => self::BASE_CURRENCY,
        ];

        $response = $this->client->post('/transfers', $requestPayload)->throw()->json();

        return ['client_reference' => data_get($response, 'id')];
    }

    public function getIdentifier(): string
    {
        return 'flutterwave';
    }
}
