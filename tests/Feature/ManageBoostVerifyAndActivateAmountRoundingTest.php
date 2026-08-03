<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ManageBoost;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the amount-verification guard in
 * Admin\ManageBoost::verifyAndActivate() — the manual "verify with
 * Flutterwave" action an admin triggers for a boost. It compared
 * Flutterwave's verified (rounded) amount against the boost's unrounded
 * stored `amount`, incorrectly refusing to activate a legitimately-paid
 * boost priced with a fractional discount (e.g. 1500.03).
 */
class ManageBoostVerifyAndActivateAmountRoundingTest extends TestCase
{
    use RefreshDatabase;

    private function makeBoost(string $amount): AdvertBoost
    {
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
            'description'  => 'Test description for admin rounding-guard fixture.',
            'state'        => 'Kampala',
            'lga'          => 'Kampala',
            'ad_status'    => 'active',
            'views'        => '0',
        ]);

        return AdvertBoost::create([
            'advert_id'         => $advert->id,
            'user_id'           => $seller->user_id,
            'payment_reference' => 'ref_' . uniqid(),
            'amount'            => $amount,
            'boost_type'        => 'top',
            'duration'          => 7,
            'boost_status'      => 'pending',
            'payment_status'    => 'pending',
        ]);
    }

    public function test_verify_and_activate_accepts_verified_amount_that_rounds_down_from_expected(): void
    {
        $boost = $this->makeBoost('1500.03'); // fractional expected total

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 888111,
                    'status'   => 'successful',
                    'amount'   => 1500, // Flutterwave-rounded amount actually charged
                    'currency' => config('currency.code'),
                ],
            ], 200),
        ]);

        $response = app(ManageBoost::class)->verifyAndActivate($boost->id);

        $response->getSession()->get('status');
        $this->assertSame('success', $response->getSession()->get('status')['type']);

        $this->assertDatabaseHas('advert_boosts', [
            'id'             => $boost->id,
            'payment_status' => 'paid',
            'boost_status'   => 'active',
        ]);
    }

    public function test_verify_and_activate_still_rejects_amount_genuinely_short_of_expected(): void
    {
        $boost = $this->makeBoost('1500.03');

        Http::fake([
            '*/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data'   => [
                    'id'       => 888112,
                    'status'   => 'successful',
                    'amount'   => 1000, // genuinely short
                    'currency' => config('currency.code'),
                ],
            ], 200),
        ]);

        $response = app(ManageBoost::class)->verifyAndActivate($boost->id);

        $this->assertSame('error', $response->getSession()->get('status')['type']);

        $this->assertDatabaseHas('advert_boosts', [
            'id'             => $boost->id,
            'payment_status' => 'pending',
            'boost_status'   => 'pending',
        ]);
    }
}
