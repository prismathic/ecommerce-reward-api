<?php

namespace App\Listeners;

use App\Events\User\AchievementUnlocked;
use App\Events\User\BadgeUnlocked;
use App\Mail\AchievementUnlockedMail;
use App\Mail\BadgeUnlockedMail;
use Illuminate\Support\Facades\Mail;

class RewardUnlocked
{
    /**
     * Send a mail notifying the user of an unlocked achievement.
     *
     * @param \App\Events\User\AchievementUnlocked $event
     *
     * @return void
     */
    public function sendAchievementUnlockedMail(AchievementUnlocked $event)
    {
        Mail::to($event->user)->send(new AchievementUnlockedMail($event->achievementName));
    }

    /**
     * Send a mail notifying the user of an unlocked badge.
     *
     * @param \App\Events\User\BadgeUnlocked $event
     *
     * @return void
     */
    public function sendBadgeUnlockedMail(BadgeUnlocked $event)
    {
        Mail::to($event->user)->send(new BadgeUnlockedMail($event->badgeName));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     *
     * @return array
     */
    public function subscribe($events)
    {
        return [
            AchievementUnlocked::class => 'sendAchievementUnlockedMail',
            BadgeUnlocked::class => 'sendBadgeUnlockedMail',
        ];
    }
}
