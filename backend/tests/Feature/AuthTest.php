<?php

namespace Tests\Feature;

use App\Models\Cart;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_csrf_cookie_endpoint_returns_successful_response(): void {
        $this->getJson('/api/csrf-cookie')->assertSuccessful();
    }

    public function test_register_creates_a_user_and_logs_them_in(): void {
        $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.user.name', 'Jane Doe')
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.cart', []);

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_register_rejects_a_duplicate_email(): void {
        $this->createUser([
            'email' => 'jane@example.com',
        ]);

        $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'An account with this email already exists.');
    }

    public function test_register_rejects_mismatched_password_confirmation(): void {
        $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'something-else',
        ])->assertStatus(422);
    }

    public function test_login_succeeds_with_correct_credentials(): void {
        $this->createUser([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.cart', []);

        $this->assertAuthenticated('web');
    }

    public function test_login_response_includes_the_users_saved_cart_items(): void {
        $user = $this->createUser([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);
        $product = $this->createProduct();
        $this->createProductStock($product);
        $cart = Cart::create(['user_id' => $user->id, 'status' => 0]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2, 'status' => 0]);

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.cart.0.slug', $product->slug)
            ->assertJsonPath('data.cart.0.quantity', 2);
    }

    public function test_login_fails_with_wrong_password(): void {
        $this->createUser([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);

        $this->assertGuest('web');
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void {
        $this->createUser([
            'email' => 'throttled@example.com',
            'password' => 'password123',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'throttled@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/auth/login', [
            'email' => 'throttled@example.com',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', fn (string $message) => str_contains($message, 'Too many login attempts'));

        $this->assertGuest('web');
    }

    public function test_logout_clears_the_session(): void {
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->withHeader('Referer', 'http://127.0.0.1:5173')
            ->postJson('/api/auth/logout')
            ->assertSuccessful();

        $this->assertGuest('web');
    }

    public function test_profile_returns_the_authenticated_user(): void {
        $user = $this->createUser([
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/auth/profile')
            ->assertSuccessful()
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.user.public_id', $user->public_id)
            ->assertJsonMissingPath('data.user.id');
    }

    public function test_profile_returns_null_user_when_guest(): void {
        $this->getJson('/api/auth/profile')
            ->assertSuccessful()
            ->assertJsonPath('data.user', null);
    }

    public function test_profile_returns_null_cart_when_guest(): void {
        $this->getJson('/api/auth/profile')
            ->assertSuccessful()
            ->assertJsonPath('data.cart', null);
    }

    public function test_profile_returns_an_empty_cart_when_the_user_has_no_saved_items(): void {
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->getJson('/api/auth/profile')
            ->assertSuccessful()
            ->assertJsonPath('data.cart', []);
    }

    public function test_profile_returns_the_users_saved_cart_items(): void {
        $user = $this->createUser();
        $product = $this->createProduct(null, ['title' => 'Saved Book']);
        $this->createProductStock($product, ['price' => '12.50']);
        $cart = Cart::create(['user_id' => $user->id, 'status' => 0]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 3, 'status' => 0]);

        $this->actingAs($user, 'web')
            ->getJson('/api/auth/profile')
            ->assertSuccessful()
            ->assertJsonPath('data.cart.0.slug', $product->slug)
            ->assertJsonPath('data.cart.0.title', 'Saved Book')
            ->assertJsonPath('data.cart.0.quantity', 3)
            ->assertJsonPath('data.cart.0.price', '12.50');
    }

    public function test_forgot_password_sends_a_reset_link_notification(): void {
        Notification::fake();

        $user = $this->createUser([
            'email' => 'jane@example.com',
        ]);

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'jane@example.com',
        ])->assertSuccessful();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_error_for_unknown_email(): void {
        Notification::fake();

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'unknown@example.com',
        ])->assertStatus(422);
    }

    public function test_reset_password_resets_the_password_and_allows_login(): void {
        Notification::fake();

        $user = $this->createUser([
            'email' => 'jane@example.com',
            'password' => 'old-password',
        ]);

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'jane@example.com',
        ])->assertSuccessful();

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'jane@example.com',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertSuccessful();

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'new-password123',
        ])->assertSuccessful();

        $this->assertAuthenticated('web');
    }

    public function test_reset_password_rejects_an_invalid_token(): void {
        $this->createUser([
            'email' => 'jane@example.com',
        ]);

        $this->postJson('/api/auth/reset-password', [
            'token' => 'not-a-real-token',
            'email' => 'jane@example.com',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertStatus(422);
    }
}
