<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TravelOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(bool $isAdmin = false): User
    {
        return User::factory()->create(['is_admin' => $isAdmin]);
    }

    private function actingWithJwt(User $user): static
    {
        $token = auth()->login($user);
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    public function test_user_can_create_travel_order(): void
    {
        $user = $this->makeUser();

        $response = $this->actingWithJwt($user)
            ->postJson('/api/travel-orders', [
                'destination'    => 'Rio de Janeiro',
                'departure_date' => '2026-05-01',
                'return_date'    => '2026-05-10',
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('order.destination', 'Rio de Janeiro')
                 ->assertJsonPath('order.status', 'solicitado');
    }

    public function test_user_can_list_own_orders(): void
    {
        $user  = $this->makeUser();
        $other = $this->makeUser();

        TravelOrder::factory()->create(['user_id' => $user->id]);
        TravelOrder::factory()->create(['user_id' => $other->id]);

        $response = $this->actingWithJwt($user)
            ->getJson('/api/travel-orders');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_admin_can_update_status(): void
    {
        $admin = $this->makeUser(isAdmin: true);
        $user  = $this->makeUser();

        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingWithJwt($admin)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'aprovado',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('order.status', 'aprovado');
    }

    public function test_regular_user_cannot_update_status(): void
    {
        $user  = $this->makeUser();
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingWithJwt($user)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'aprovado',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_cancel_approved_order(): void
    {
        $admin = $this->makeUser(isAdmin: true);
        $user  = $this->makeUser();

        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status'  => 'aprovado',
        ]);

        $response = $this->actingWithJwt($admin)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'cancelado',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_update_status_of_non_pending_order(): void
    {
        $admin = $this->makeUser(isAdmin: true);
        $user  = $this->makeUser();

        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status'  => 'aprovado',
        ]);

        $response = $this->actingWithJwt($admin)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'cancelado',
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_approve_cancelled_order(): void
    {
        $admin = $this->makeUser(isAdmin: true);
        $user  = $this->makeUser();

        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status'  => 'cancelado',
        ]);

        $response = $this->actingWithJwt($admin)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'aprovado',
            ]);

        $response->assertStatus(422);
    }

    public function test_regular_user_cannot_approve_order(): void
    {
        $user  = $this->makeUser();
        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status'  => 'solicitado',
        ]);

        $response = $this->actingWithJwt($user)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'aprovado',
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_cancel_order(): void
    {
        $user  = $this->makeUser();
        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status'  => 'solicitado',
        ]);

        $response = $this->actingWithJwt($user)
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'cancelado',
            ]);

        $response->assertStatus(403);
    }
}
