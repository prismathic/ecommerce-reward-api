<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testItLogsAValidUserInSuccessfully()
    {
        $user = User::factory()->create();

        $loginPayload = ['email' => $user->email, 'password' => 'password'];

        $response = $this->post('api/auth/login', $loginPayload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                ],
            ]);

        $this->assertAuthenticatedViaSanctum($user);
    }

    public function testItReturnsAnUnauthenticatedResponseWhenIncorrectCredentialsArePassed()
    {
        $loginPayload = ['email' => $this->faker->safeEmail(), 'password' => 'incorrectPassword'];

        $response = $this->post('api/auth/login', $loginPayload);

        $response->assertUnauthorized()
            ->assertJson([
                'status' => false,
                'message' => 'Invalid credentials.',
            ]);
    }

    public function testItReturnsABadRequestResponseWhenAnInvalidEmailIsPassed()
    {
        $loginPayload = ['email' => $this->faker->word(), 'password' => 'incorrectPassword'];

        $response = $this->post('api/auth/login', $loginPayload);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'status' => false,
                'message' => 'The email must be a valid email address.',
            ]);
    }

    public function testItLogsAnAuthenticatedUserOutSuccessfully()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->post('api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Logged out successfully.',
            ]);
    }

    /**
     * Assert a sanctum token was created for a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     *
     * @return void
     */
    private function assertAuthenticatedViaSanctum(Authenticatable $user)
    {
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id' => $user->id,
        ]);
    }
}
