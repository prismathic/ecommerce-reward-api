<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CashbackPayment extends Model
{
    use HasFactory;

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $guarded = ['id'];

    public const STATUSES = [
        'PENDING' => 'pending',
        'SUCCESSFUL' => 'successful',
        'FAILED' => 'failed',
    ];

    public static function generateReference(Order $order): string
    {
        do {
            $reference = vsprintf('%s-%s', [$order->id, Str::random(4)]);
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
