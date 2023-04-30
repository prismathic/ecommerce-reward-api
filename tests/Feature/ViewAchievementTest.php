<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\User;
use Database\Seeders\AchievementSeeder;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewAchievementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AchievementSeeder::class, BadgeSeeder::class]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testItRetrievesAUsersAchievementsSuccessfully()
    {
        $user = User::factory()->create();

        $unlockedAchievement = Achievement::first();
        $nextAvailableAchievements = Achievement::where('required_purchase_count', '>', $unlockedAchievement->required_purchase_count)
            ->orderBy('required_purchase_count')
            ->pluck('name')
            ->toArray();

        $currentBadge = Badge::first();
        $expectedNextBadge = Badge::where('required_achievement_count', '>', $currentBadge->required_achievement_count)
            ->orderBy('required_achievement_count')
            ->first();

        $user->achievements()->attach($unlockedAchievement->id, ['unlocked_at' => now()]);
        $user->update(['current_badge_id' => $currentBadge->id]);

        $response = $this->get("/api/users/{$user->id}/achievements");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'unlocked_achievements',
                    'next_available_achievements',
                    'current_badge',
                    'next_badge',
                    'remaining_to_unlock_next_badge',
                ],
            ]);

        $responseData = data_get($response->decodeResponseJson(), 'data');

        $this->assertCount(1, $responseData['unlocked_achievements']);
        $this->assertEquals([$unlockedAchievement->name], $responseData['unlocked_achievements']);

        $this->assertCount(Achievement::count() - 1, $responseData['next_available_achievements']);
        $this->assertNotContains($unlockedAchievement->name, $responseData['next_available_achievements']);
        $this->assertEquals($nextAvailableAchievements, $responseData['next_available_achievements']);

        $this->assertSame($currentBadge->name, $responseData['current_badge']);
        $this->assertSame($expectedNextBadge->name, $responseData['next_badge']);
        $this->assertEquals(
            $expectedNextBadge->required_achievement_count - $user->achievements()->count(),
            $responseData['remaining_to_unlock_next_badge'],
        );
    }

    public function testItReturnsANotFoundErrorIfTheUserIdPassedDoesNotExist()
    {
        $invalidUserId = $this->faker->word();

        $response = $this->get("/api/users/{$invalidUserId}/achievements");

        $response->assertNotFound()
            ->assertJson([
                'status' => false,
                'message' => 'Resource not found.',
            ]);
    }
}
