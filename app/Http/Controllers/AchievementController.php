<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\AchievementResource;
use App\Models\User;

class AchievementController extends Controller
{
    /**
     * Retrieve a user's achievement metrics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        $user->load(['achievements', 'current_badge']);

        return $this->okResponse('Achievement data retrieved successfully.', new AchievementResource($user));
    }
}
