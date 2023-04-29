<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashbackPayment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'PENDING' => 'pending',
        'SUCCESSFUL' => 'successful',
        'FAILED' => 'failed',
    ];
}
