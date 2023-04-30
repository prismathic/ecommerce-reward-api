<?php

namespace App\Jobs;

use App\Events\User\AchievementUnlocked;
use App\Events\User\BadgeUnlocked;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private User $user, private array $orderData)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();

        $product = Product::findOrFail($this->orderData['product']);

        try {
            $this->user->orders()->create([
                'product_id' => $product->id,
                'quantity' => $this->orderData['quantity'],
                'total' => $product->price * $this->orderData['quantity'],
            ]);

            $this->verifyIfPurchaseUnlocksAchievement();

            DB::commit();

            //send order completion mail
        } catch (\Throwable $th) {
            report($th); //report exception for logging

            DB::rollBack();

            //send mail if order cannot be processed
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
        $purchaseCount = $this->user->orders()->count();
        $unlockableAchievement = Achievement::where('required_purchase_count', $purchaseCount)->first();

        if (! $unlockableAchievement) {
            return;
        }

        $this->user->achievements()->attach($unlockableAchievement->id, ['unlocked_at' => now()]);

        event(new AchievementUnlocked($unlockableAchievement->name, $this->user));

        $newAchievementCount = $this->user->achievements()->count();
        $unlockableBadge = Badge::where('required_achievement_count', $newAchievementCount)->first();

        if (! $unlockableBadge) {
            return;
        }

        $this->user->update(['current_badge_id' => $unlockableBadge->id]);

        event(new BadgeUnlocked($unlockableBadge->name, $this->user));
    }
}
