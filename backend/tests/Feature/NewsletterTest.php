<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribes_a_new_email(): void
    {
        $this->postJson('/api/newsletter', [
            'email' => 'reader@example.com',
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.email', 'reader@example.com')
            ->assertJsonPath('data.status', 1);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'reader@example.com',
        ]);
    }

    public function test_rejects_an_already_subscribed_email(): void
    {
        NewsletterSubscriber::create([
            'email' => 'reader@example.com',
            'status' => 1,
        ]);

        $this->postJson('/api/newsletter', [
            'email' => 'reader@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'This email is already subscribed.');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_rejects_an_invalid_email(): void
    {
        $this->postJson('/api/newsletter', [
            'email' => 'not-an-email',
        ])
            ->assertStatus(422);
    }
}
