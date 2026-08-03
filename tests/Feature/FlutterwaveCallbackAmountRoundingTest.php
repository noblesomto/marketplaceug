<?php

namespace Tests\Feature;

use App\Http\Controllers\User\FlutterwaveController;
use App\Models\Advert;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Covers the amount-verification guard in User\FlutterwaveController::callback().
 *
 * The site rounds the total when it *initializes* the Flutterwave charge
 * (`round($shipping['grand_total'])`), but the guard was comparing the
 * verified amount against the unrounded `amount_paid` stored on the
 * Payment. Any booking whose true total has a fractional remainder that
 * rounds down (e.g. commission/boost pricing landing on 1545.03) was
 * incorrectly rejected even though Flutterwave had already charged the
 * customer the rounded amount successfully.
 */
class FlutterwaveCallbackAmountRoundingTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingPayment(User $seller, User $buyer, string $amountPaid): Payment
    {
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
            'buy_direct'   => 'Yes',
            'description'  => 'Test description for rounding-guard fixture.',
            'state'        => 'Kampala',
            'lga'          => 'Kampala',
            'ad_status'    => 'active',
            'views'        => '0',
        ]);

        return Payment::create([
            'advert_id'         => $advert->id,
            'order_code'        => strtoupper(substr(uniqid(), -8)),
            'user_id'           => $buyer->user_id,
            'payment_reference' => 'ref_' . uniqid(),
            'first_name'        => $buyer->name,
            'last_name'         => 'Buyer',
            'phone'             => '0771234567',
            'amount'            => '1500',
            'commission'        => '45.03',
            'amount_paid'       => $amountPaid,
            'payment_status'    => 'pending',
        ]);
    }

    public function test_callback_accepts_verified_amount_that_rounds_down_from_expected(): void
    {
        Mail::fake();
        Queue::fake();

        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        // True expected total has a fractional remainder (e.g. 2-3% commission).
        // Flutterwave was charged the rounded amount at initialize() time and
        // reports back the rounded integer on verify.
        $payment = $this->makePendingPayment($seller, $buyer, '1545.03');

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 999111,
                    'status'   => 'successful',
                    'amount'   => 1545, // rounded down from 1545.03 — must NOT be rejected
                    'currency' => config('currency.code'),
                    'paid_at'  => now()->toISOString(),
                    'metadata' => ['advert_id' => $payment->advert_id],
                ],
            ], 200),
        ]);

        $request = Request::create('/payment/callback', 'GET', ['tx_ref' => $payment->payment_reference]);

        $response = app(FlutterwaveController::class)->callback($request);

        $this->assertSame(route('buy.direct.success'), $response->getTargetUrl());

        $this->assertDatabaseHas('payments', [
            'id'             => $payment->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_callback_still_rejects_amount_genuinely_short_of_expected(): void
    {
        Mail::fake();
        Queue::fake();

        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        $payment = $this->makePendingPayment($seller, $buyer, '1545.03');

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 999112,
                    'status'   => 'successful',
                    'amount'   => 1000, // genuinely short — must still be rejected
                    'currency' => config('currency.code'),
                    'paid_at'  => now()->toISOString(),
                    'metadata' => ['advert_id' => $payment->advert_id],
                ],
            ], 200),
        ]);

        $request = Request::create('/payment/callback', 'GET', ['tx_ref' => $payment->payment_reference]);

        $response = app(FlutterwaveController::class)->callback($request);

        $this->assertSame(route('payment.failed'), $response->getTargetUrl());

        $this->assertDatabaseHas('payments', [
            'id'             => $payment->id,
            'payment_status' => 'pending',
        ]);
    }
}
