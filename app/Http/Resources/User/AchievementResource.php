<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $currentBadge = $this->currentBadge;
        $nextBadge = $this->nextBadge();
        $differenceBetweenNextAndCurrentBadge = $nextBadge
            ? $nextBadge->required_achievement_count - $currentBadge->required_achievement_count
            : 0;

        return [
            'unlocked_achievements' => $this->getUnlockedAchievements(),
            'next_available_achievements' => $this->getNextAvailableAchievements(),
            'current_badge' => $currentBadge->name,
            'next_badge' => $nextBadge->name ?? null,
            'remaining_to_unlock_next_badge' => $differenceBetweenNextAndCurrentBadge,
        ];
    }
}
