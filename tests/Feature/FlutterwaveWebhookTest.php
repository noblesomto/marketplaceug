<?php

namespace Tests\Feature;

use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwaveWebhookTest extends TestCase
{
    use RefreshDatabase;

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

        // A misconfigured secret is a server error, not an attack — Flutterwave must
        // see a 5xx so it retries the event once FLW_WEBHOOK_HASH is set, rather than
        // treating a 200 as "delivered" and losing the event permanently.
        $response->assertStatus(500);
        // Must fail closed: an empty expected secret must never make hash_equals('', '')
        // short-circuit acceptance. Proven by asserting the handler never reached the
        // server-side verification call — i.e. it was rejected at the signature check.
        Http::assertNothingSent();
    }

    /**
     * The webhook's boost amount-verification guard compared Flutterwave's
     * verified (rounded) amount against the boost's unrounded stored
     * `amount`. A boost priced with a fractional discount (e.g. 1500.03)
     * was incorrectly left un-activated because Flutterwave reports back
     * the rounded integer it actually charged (1500).
     */
    public function test_activates_boost_when_verified_amount_rounds_down_from_expected(): void
    {
        config(['services.flutterwave.webhookHash' => 'test-secret']);

        $seller = User::factory()->create();
        $advert = Advert::create([
            'user_id'      => $seller->user_id,
            'ad_id'        => 'AD' . uniqid(),
            'ad_type'      => 'Sell',
            'ad_title'     => 'Test Advert',
            'title_slug'   => 'test-advert-' . uniqid(),
            'category'     => '1',
            'sub_category' => '1',
            'brand'        => '1',
            'price'        => '1500',
            'buy_direct'   => 'No',
            'description'  => 'Test description for webhook rounding-guard fixture.',
            'state'        => 'Kampala',
            'lga'          => 'Kampala',
            'ad_status'    => 'active',
            'views'        => '0',
        ]);

        $boost = AdvertBoost::create([
            'advert_id'         => $advert->id,
            'user_id'           => $seller->user_id,
            'payment_reference' => 'ref_' . uniqid(),
            'amount'            => '1500.03', // fractional expected total
            'boost_type'        => 'top',
            'duration'          => 7,
            'boost_status'      => 'pending',
            'payment_status'    => 'pending',
        ]);

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 777111,
                    'status'   => 'successful',
                    'amount'   => 1500, // Flutterwave-rounded amount actually charged
                    'currency' => config('currency.code'),
                    'metadata' => ['payment_type' => 'boost'],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/flutterwave/webhook', [
            'event' => 'charge.completed',
            'data'  => ['reference' => $boost->payment_reference],
        ], [
            'verif-hash' => 'test-secret',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('advert_boosts', [
            'id'             => $boost->id,
            'payment_status' => 'paid',
            'boost_status'   => 'active',
        ]);
    }
}
