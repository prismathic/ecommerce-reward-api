<?php

namespace App\Jobs;

use App\Events\User\AchievementUnlocked;
use App\Events\User\BadgeUnlocked;
use App\Exceptions\CannotProcessOrder;
use App\Mail\OrderFailed;
use App\Mail\OrderProcessed;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Order $order)
    {
        $this->user = $this->order->user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->status !== Order::STATUS_PENDING) {
            $this->fail(new CannotProcessOrder('A non-pending order cannot be processed.'));

            return;
        }

        $this->order->update(['status' => Order::STATUS_PROCESSING]);

        DB::beginTransaction();

        try {
            $this->order->update(['status' => Order::STATUS_PROCESSED]);

            $this->verifyIfPurchaseUnlocksAchievement();

            Mail::to($this->user)->send(new OrderProcessed($this->order));

            DB::commit();
        } catch (\Throwable $th) {
            report($th); //report exception for logging
            logger()->error("Failed to process order {$this->order->id}. Reason: {$th->getMessage()}");

            DB::rollBack();

            $this->order->update(['status' => Order::STATUS_FAILED]);

            Mail::to($this->user)->send(new OrderFailed($this->order));
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new WithoutOverlapping($this->user->id)]; //ensure no two orders from the same user are processed simultaneously.
    }

    /**
     * Verify that a certain purchase unlocks an achievement and subsequently a badge.
     *
     * @return void
     */
    private function verifyIfPurchaseUnlocksAchievement(): void
    {
        $purchaseCount = $this->user->orders()->processed()->count();
        $unlockableAchievement = Achievement::where('required_purchase_count', $purchaseCount)->first();

        if (! $unlockableAchievement) {
            return;
        }

        $this->user->achievements()->syncWithoutDetaching([$unlockableAchievement->id => ['unlocked_at' => now()]]);

        event(new AchievementUnlocked($unlockableAchievement->name, $this->user));

        $newAchievementCount = $this->user->achievements()->count();
        $unlockableBadge = Badge::where('required_achievement_count', $newAchievementCount)->first();

        if (! $unlockableBadge) {
            return;
        }

        $this->user->update(['current_badge_id' => $unlockableBadge->id]);

        event(new BadgeUnlocked($unlockableBadge->name, $this->user, $this->order));
    }
}
