<?php

namespace Tests\Feature\User;

use App\Events\User\AchievementUnlocked;
use App\Events\User\BadgeUnlocked;
use App\Http\Clients\PaymentClient;
use App\Jobs\ProcessOrder;
use App\Mail\BadgeUnlockedMail;
use App\Mail\OrderProcessed;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\CashbackPayment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AchievementSeeder;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Mocks\PaymentClientMock;
use Tests\TestCase;

class ProcessOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AchievementSeeder::class, BadgeSeeder::class]);
    }

    public function testItQueuesAnOrderToBeProcessedSuccessfully()
    {
        Queue::fake();

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING,
            'quantity' => $payload['quantity'],
        ]);

        Queue::assertPushed(ProcessOrder::class);
    }

    public function testItCannotProcessAnOrderIfTheProductPassedIsInvalid()
    {
        Queue::fake();

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload(['product' => $this->faker->word()]);

        $response = $this->post('api/orders', $payload);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'status' => false,
                'message' => 'The selected product is invalid.',
            ]);

        $this->assertDatabaseMissing('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING,
            'quantity' => $payload['quantity'],
        ]);

        Queue::assertNotPushed(ProcessOrder::class);
    }

    public function testItProcessesAnOrderSuccessfully()
    {
        Mail::fake();

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();

        Order::factory()->create(['user_id' => $user->id, 'status' => Order::STATUS_PROCESSED]);

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        Mail::assertSent(OrderProcessed::class);
    }

    public function testItUnlocksAnAchievementAfterFulfillingARequiredNumberOfOrders()
    {
        Mail::fake();
        Event::fake([AchievementUnlocked::class, BadgeUnlocked::class]);

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();

        $randomAchievement = Achievement::inRandomOrder()->first();

        Order::factory($randomAchievement->required_purchase_count - 1)
            ->create(['user_id' => $user->id, 'status' => Order::STATUS_PROCESSED]);

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        Mail::assertSent(OrderProcessed::class);
        Event::assertDispatched(function (AchievementUnlocked $event) use ($randomAchievement, $user) {
            return $event->user->id === $user->id && $event->achievementName === $randomAchievement->name;
        });
    }

    public function testItDoesNotUnlockAnAchievementIfTheRequiredNumberOfOrdersAreNotFulfilled()
    {
        Mail::fake();
        Event::fake([AchievementUnlocked::class, BadgeUnlocked::class]);

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();

        $lastAchievement = Achievement::orderByDesc('required_purchase_count')->first();

        Order::factory($lastAchievement->required_purchase_count - 2)
            ->create(['user_id' => $user->id, 'status' => Order::STATUS_PROCESSED]);

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        Mail::assertSent(OrderProcessed::class);
        Event::assertNotDispatched(AchievementUnlocked::class);
    }

    public function testItUnlocksABadgeAfterFulfillingARequiredNumberOfAchievements()
    {
        Mail::fake();
        Event::fake([AchievementUnlocked::class, BadgeUnlocked::class]);

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();
        $firstBadge = Badge::first();

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        Mail::assertSent(OrderProcessed::class);
        Event::assertDispatched(function (BadgeUnlocked $event) use ($firstBadge, $user) {
            return $event->user->id === $user->id && $event->badgeName === $firstBadge->name;
        });
    }

    public function testItDoesNotUnlockABadgeIfTheRequiredNumberOfAchievementsAreNotFulfilled()
    {
        Mail::fake();
        Event::fake([AchievementUnlocked::class, BadgeUnlocked::class]);

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();

        Order::factory()->create(['user_id' => $user->id, 'status' => Order::STATUS_PROCESSED]);

        $user->unlockAchievement(Achievement::first()); //unlock just one achievement since the last badge has 5 achievements required.

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        Mail::assertSent(OrderProcessed::class);
        Event::assertNotDispatched(BadgeUnlocked::class);
    }

    public function testItProcessesACashbackPaymentWhenABadgeIsUnlocked()
    {
        Mail::fake();
        $this->instance(PaymentClient::class, new PaymentClientMock());

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();
        $firstBadge = Badge::first();

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        $this->assertDatabaseHas('cashback_payments', [
            'amount' => User::BADGE_UNLOCKING_CASHBACK_AMOUNT,
            'status' => CashbackPayment::STATUSES['PROCESSING'],
            'reason' => "Badge unlocked: {$firstBadge->name}",
            'account_number' => $user->account_number,
            'bank_code' => $user->bank_code,
        ]);

        Mail::assertSent(OrderProcessed::class);
        Mail::assertQueued(BadgeUnlockedMail::class);
    }

    public function testItSetsACashbackPaymentAsFailedWhenCallToPaymentClientIsUnsuccessful()
    {
        Mail::fake();
        $this->instance(PaymentClient::class, (new PaymentClientMock())->switchState('failed'));

        $user = $this->createAuthenticatedUser();
        $payload = $this->getOrderCreationPayload();
        $firstBadge = Badge::first();

        $response = $this->post('api/orders', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_id',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $payload['product'],
            'user_id' => $user->id,
            'status' => Order::STATUS_PROCESSED,
            'quantity' => $payload['quantity'],
        ]);

        $this->assertDatabaseHas('cashback_payments', [
            'amount' => User::BADGE_UNLOCKING_CASHBACK_AMOUNT,
            'status' => CashbackPayment::STATUSES['FAILED'],
            'reason' => "Badge unlocked: {$firstBadge->name}",
            'account_number' => $user->account_number,
            'bank_code' => $user->bank_code,
        ]);

        Mail::assertSent(OrderProcessed::class);
        Mail::assertQueued(BadgeUnlockedMail::class);
    }

    /**
     * Create an authenticated user to test with.
     *
     * @param \App\Models\User|null $user
     *
     * @return \App\Models\User
     */
    private function createAuthenticatedUser(?User $user = null): User
    {
        $user = $user ?? User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * Get a sample payload to use for order creation.
     *
     * @param array|null $substitutingPayload
     *
     * @return array
     */
    private function getOrderCreationPayload(?array $substitutingPayload = []): array
    {
        return [
            'product' => $substitutingPayload['product'] ?? Product::factory()->create()->id,
            'quantity' => $substitutingPayload['quantity'] ?? $this->faker->numberBetween(1, 10),
            'discount_code' => $substitutingPayload['discount_code'] ?? null,
        ];
    }
}
