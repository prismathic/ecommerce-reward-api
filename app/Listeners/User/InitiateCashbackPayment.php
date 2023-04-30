<?php

namespace App\Listeners\User;

use App\Events\User\BadgeUnlocked;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;

class InitiateCashbackPayment implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private PaymentService $paymentService)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\User\BadgeUnlocked $event
     *
     * @return void
     */
    public function handle(BadgeUnlocked $event)
    {
        try {
            $this->paymentService->initiateCashbackPayment(
                $event->order,
                User::BADGE_UNLOCKING_CASHBACK_AMOUNT,
                "Badge unlocked: {$event->badgeName}"
            );
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
