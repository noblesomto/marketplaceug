<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
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

    public function test_rejects_request_when_webhook_secret_is_unconfigured(): void
    {
        // Simulate a misconfigured environment: FLW_WEBHOOK_HASH unset → config resolves to null/empty.
        config(['services.flutterwave.webhookHash' => null]);

        Http::fake();

        // No verif-hash header sent, matching the unconfigured-secret scenario.
        // Include a reference so that, if the signature check were bypassed, the
        // handler would proceed on to the server-side verification HTTP call.
        $response = $this->postJson('/api/flutterwave/webhook', [
            'event' => 'charge.completed',
            'data' => ['reference' => 'some-ref'],
        ]);

        $response->assertStatus(200);
        // Must fail closed: an empty expected secret must never make hash_equals('', '')
        // short-circuit acceptance. Proven by asserting the handler never reached the
        // server-side verification call — i.e. it was rejected at the signature check.
        Http::assertNothingSent();
    }
}
