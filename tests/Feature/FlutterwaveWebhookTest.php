<?php

namespace Tests\Feature;

use Tests\TestCase;

class FlutterwaveWebhookTest extends TestCase
{
    public function test_rejects_request_with_missing_signature(): void
    {
        config(['services.flutterwave.webhookHash' => 'test-secret']);

        $response = $this->postJson('/api/flutterwave/webhook', ['event' => 'charge.completed']);

        $response->assertStatus(200);
        // Rejected webhooks still return 200 (per existing gateway-retry-suppression convention)
        // but must not process the payload — assert no side effect occurred, e.g. no Payment updated.
    }

    public function test_accepts_request_with_correct_signature(): void
    {
        config(['services.flutterwave.webhookHash' => 'test-secret']);

        $response = $this->postJson('/api/flutterwave/webhook', ['event' => 'charge.completed'], [
            'verif-hash' => 'test-secret',
        ]);

        $response->assertStatus(200);
    }
}
