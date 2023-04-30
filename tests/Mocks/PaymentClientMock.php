<?php

namespace Tests\Mocks;

use App\Dtos\PaymentData;
use App\Http\Clients\PaymentClient;
use Exception;
use InvalidArgumentException;

class PaymentClientMock extends PaymentClient
{
    private string $state = 'success';

    public function initiatePayout(PaymentData $paymentData): array
    {
        if ($this->state === 'failed') {
            throw new Exception('Failed request.');
        }

        return ['client_reference' => 'good reference'];
    }

    public function getIdentifier(): string
    {
        return 'mock_client';
    }

    /**
     * Switch the state of the class to give a different response type.
     *
     * @param string $newState
     *
     * @return self
     */
    public function switchState(string $newState): self
    {
        if (! in_array($newState, ['success', 'failed'])) {
            throw new InvalidArgumentException("Invalid state: $newState passed");
        }

        $this->state = $newState;

        return $this;
    }
}
