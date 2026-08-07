<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_request_a_ticket(): void {
        $response = $this->postJson('/api/broadcasting/auth');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_receives_a_valid_signed_ticket(): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/broadcasting/auth');

        $response->assertOk();

        $ticket = $response->json('data.ticket');
        $this->assertNotEmpty($ticket);

        [$payload, $signature] = explode('.', $ticket);
        $expected_signature = hash_hmac('sha256', $payload, config('websocket.ticket_secret'));
        $this->assertEquals($expected_signature, $signature);

        $data = json_decode(base64_decode($payload), true);
        $this->assertEquals($user->public_id, $data['public_id']);
        $this->assertGreaterThan(time(), $data['expires_at']);
    }
}
