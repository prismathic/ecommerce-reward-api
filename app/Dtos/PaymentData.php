<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        public string $bank_code,
        public string $account_number,
        public float $amount,
        public string $reason,
        public string $reference,
    ) {
    }
}
