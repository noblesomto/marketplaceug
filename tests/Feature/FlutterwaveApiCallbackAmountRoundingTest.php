<?php

namespace Tests\Feature;

use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Covers the two amount-verification guards in
 * Api\FlutterwaveController::handleCallback() — the buy-direct guard
 * (handleBuyDirectPayment) and the boost guard (handleBoostPayment).
 *
 * Both compared Flutterwave's verified (rounded) amount against an
 * unrounded stored value, incorrectly rejecting legitimate payments whose
 * true total has a fractional remainder that rounds down.
 */
class FlutterwaveApiCallbackAmountRoundingTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdvert(User $seller): Advert
    {
        return Advert::create([
            'user_id'      => $seller->user_id,
            'ad_id'        => 'AD' . uniqid(),
            'ad_type'      => 'Sell',
            'ad_title'     => 'Test Advert',
            'title_slug'   => 'test-advert-' . uniqid(),
            'category'     => '1',
            'sub_category' => '1',
            'brand'        => '1',
            'price'        => '1500',
            'buy_direct'   => 'Yes',
            'description'  => 'Test description for API rounding-guard fixture.',
            'state'        => 'Kampala',
            'lga'          => 'Kampala',
            'ad_status'    => 'active',
            'views'        => '0',
        ]);
    }

    private function makeShipping(): Shipping
    {
        return Shipping::create([
            'ship_id'     => 'SHIP' . uniqid(),
            'company'     => 'Test Shipping Co',
            'weight'      => '1',
            'description' => 'Test shipping option',
            'price'       => '10',
            'logo'        => 'test-logo.png',
        ]);
    }

    public function test_buy_direct_callback_accepts_verified_amount_that_rounds_down_from_expected(): void
    {
        Mail::fake();

        $seller   = User::factory()->create();
        $buyer    = User::factory()->create();
        $advert   = $this->makeAdvert($seller);
        $shipping = $this->makeShipping();

        $payment = Payment::create([
            'advert_id'         => $advert->id,
            'order_code'        => strtoupper(substr(uniqid(), -8)),
            'user_id'           => $buyer->user_id,
            'payment_reference' => 'ref_' . uniqid(),
            'first_name'        => $buyer->name,
            'last_name'         => 'Buyer',
            'phone'             => '0771234567',
            'amount'            => '1500',
            'commission'        => '45.03',
            'amount_paid'       => '1545.03', // fractional expected total
            'shipping_method'   => $shipping->id,
            'payment_status'    => 'pending',
        ]);

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 555111,
                    'status'   => 'successful',
                    'amount'   => 1545, // Flutterwave-rounded amount actually charged
                    'currency' => config('currency.code'),
                    'metadata' => [
                        'advert_id'    => $payment->advert_id,
                        'payment_type' => 'buy_direct',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/payments/callback', [
            'tx_ref' => $payment->payment_reference,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('payments', [
            'id'             => $payment->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_buy_direct_callback_still_rejects_amount_genuinely_short_of_expected(): void
    {
        Mail::fake();

        $seller = User::factory()->create();
        $buyer  = User::factory()->create();
        $advert = $this->makeAdvert($seller);

        $payment = Payment::create([
            'advert_id'         => $advert->id,
            'order_code'        => strtoupper(substr(uniqid(), -8)),
            'user_id'           => $buyer->user_id,
            'payment_reference' => 'ref_' . uniqid(),
            'first_name'        => $buyer->name,
            'last_name'         => 'Buyer',
            'phone'             => '0771234567',
            'amount'            => '1500',
            'commission'        => '45.03',
            'amount_paid'       => '1545.03',
            'payment_status'    => 'pending',
        ]);

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 555112,
                    'status'   => 'successful',
                    'amount'   => 1000, // genuinely short
                    'currency' => config('currency.code'),
                    'metadata' => [
                        'advert_id'    => $payment->advert_id,
                        'payment_type' => 'buy_direct',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/payments/callback', [
            'tx_ref' => $payment->payment_reference,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);

        $this->assertDatabaseHas('payments', [
            'id'             => $payment->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_boost_callback_accepts_verified_amount_that_rounds_down_from_expected(): void
    {
        $seller = User::factory()->create();
        $advert = $this->makeAdvert($seller);

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
                    'id'       => 555113,
                    'status'   => 'successful',
                    'amount'   => 1500, // Flutterwave-rounded amount actually charged
                    'currency' => config('currency.code'),
                    'metadata' => [
                        'advert_id'    => $boost->advert_id,
                        'payment_type' => 'boost',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/payments/callback', [
            'tx_ref' => $boost->payment_reference,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('advert_boosts', [
            'id'             => $boost->id,
            'payment_status' => 'paid',
            'boost_status'   => 'active',
        ]);
    }
}
