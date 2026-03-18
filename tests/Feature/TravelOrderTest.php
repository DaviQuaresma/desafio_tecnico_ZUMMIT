<?php

namespace Tests\Feature;

use App\Enums\TravelOrderStatus;
use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderApprovedNotification;
use App\Notifications\TravelOrderCanceledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TravelOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    // ==================== CREATE ====================

    public function test_user_can_create_travel_order(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/travel-orders', [
                'destination' => 'São Paulo, Brasil',
                'departure_date' => '2026-04-01',
                'return_date' => '2026-04-10',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'requester',
                ],
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'user_id' => $this->user->id,
            'destination' => 'São Paulo, Brasil',
            'status' => TravelOrderStatus::REQUESTED->value,
        ]);
    }

    public function test_user_cannot_create_travel_order_with_invalid_dates(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/travel-orders', [
                'destination' => 'São Paulo, Brasil',
                'departure_date' => '2026-04-10',
                'return_date' => '2026-04-01', // before departure
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_date']);
    }

    public function test_user_cannot_create_travel_order_without_required_fields(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/travel-orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'departure_date', 'return_date']);
    }

    // ==================== LIST ====================

    public function test_user_can_list_own_travel_orders(): void
    {
        TravelOrder::factory()->count(3)->create(['user_id' => $this->user->id]);
        TravelOrder::factory()->count(2)->create(); // other user's orders

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/travel-orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_user_can_filter_by_status(): void
    {
        TravelOrder::factory()->requested()->create(['user_id' => $this->user->id]);
        TravelOrder::factory()->approved()->create(['user_id' => $this->user->id]);
        TravelOrder::factory()->canceled()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/travel-orders?status=approved');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_user_can_filter_by_destination(): void
    {
        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'destination' => 'São Paulo, Brasil',
        ]);
        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'destination' => 'Rio de Janeiro, Brasil',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/travel-orders?destination=Paulo');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_user_can_filter_by_period(): void
    {
        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2026-04-01',
            'return_date' => '2026-04-10',
        ]);
        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2026-06-01',
            'return_date' => '2026-06-10',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/travel-orders?start_date=2026-04-01&end_date=2026-04-30');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    // ==================== SHOW ====================

    public function test_user_can_view_own_travel_order(): void
    {
        $order = TravelOrder::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/travel-orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_user_cannot_view_other_users_travel_order(): void
    {
        $otherUser = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/travel-orders/{$order->id}");

        $response->assertStatus(404);
    }

    // ==================== APPROVE ====================

    public function test_user_can_approve_other_users_order(): void
    {
        Notification::fake();

        $otherUser = User::factory()->create();
        $order = TravelOrder::factory()->requested()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader())
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status.value', 'approved');

        $this->assertDatabaseHas('travel_orders', [
            'id' => $order->id,
            'status' => TravelOrderStatus::APPROVED->value,
        ]);

        Notification::assertSentTo($otherUser, TravelOrderApprovedNotification::class);
    }

    public function test_user_cannot_approve_own_order(): void
    {
        $order = TravelOrder::factory()->requested()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_cannot_approve_already_approved_order(): void
    {
        $otherUser = User::factory()->create();
        $order = TravelOrder::factory()->approved()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader())
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_approve_canceled_order(): void
    {
        $otherUser = User::factory()->create();
        $order = TravelOrder::factory()->canceled()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader())
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(422);
    }

    // ==================== CANCEL ====================

    public function test_user_can_cancel_own_requested_order(): void
    {
        Notification::fake();

        $order = TravelOrder::factory()->requested()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/travel-orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status.value', 'canceled');

        Notification::assertSentTo($this->user, TravelOrderCanceledNotification::class);
    }

    public function test_user_cannot_cancel_own_approved_order(): void
    {
        $order = TravelOrder::factory()->approved()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/travel-orders/{$order->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_other_user_can_cancel_approved_order(): void
    {
        Notification::fake();

        $otherUser = User::factory()->create();
        $order = TravelOrder::factory()->approved()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader())
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'canceled',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status.value', 'canceled');

        Notification::assertSentTo($otherUser, TravelOrderCanceledNotification::class);
    }

    public function test_cannot_cancel_already_canceled_order(): void
    {
        $otherUser = User::factory()->create();
        $order = TravelOrder::factory()->canceled()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader())
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'canceled',
            ]);

        $response->assertStatus(422);
    }

    // ==================== AUTH ====================

    public function test_unauthenticated_user_cannot_access_travel_orders(): void
    {
        $response = $this->getJson('/api/travel-orders');

        $response->assertStatus(401);
    }

    // ==================== ADMIN ====================

    public function test_admin_can_list_all_travel_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = JWTAuth::fromUser($admin);

        TravelOrder::factory()->count(3)->create(['user_id' => $this->user->id]);
        TravelOrder::factory()->count(2)->create();

        $response = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->getJson('/api/travel-orders');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_admin_can_view_any_travel_order(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = JWTAuth::fromUser($admin);

        $order = TravelOrder::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->getJson("/api/travel-orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_admin_can_approve_other_users_order(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $adminToken = JWTAuth::fromUser($admin);

        $order = TravelOrder::factory()->requested()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status.value', 'approved');

        $this->assertDatabaseHas('travel_orders', [
            'id' => $order->id,
            'status' => TravelOrderStatus::APPROVED->value,
        ]);

        Notification::assertSentTo($this->user, TravelOrderApprovedNotification::class);
    }

    public function test_admin_cannot_approve_own_order(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = JWTAuth::fromUser($admin);

        $order = TravelOrder::factory()->requested()->create(['user_id' => $admin->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
