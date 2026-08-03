<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ManagePayments;
use App\Models\Advert;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPayoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a Payment (with its Advert owned by $seller) using direct model
     * creation, since neither Payment nor Advert has a factory in this codebase.
     */
    private function makePayment(User $seller): Payment
    {
        $buyer = User::factory()->create();

        $advert = Advert::create([
            'user_id'      => $seller->user_id,
            'ad_id'        => 'AD' . uniqid(),
            'ad_type'      => 'Sell',
            'ad_title'     => 'Test Advert',
            'title_slug'   => 'test-advert-' . uniqid(),
            'category'     => '1',
            'sub_category' => '1',
            'brand'        => '1',
            'price'        => '50000',
            'buy_direct'   => 'No',
            'description'  => 'Test description for payout fixture.',
            'state'        => 'Kampala',
            'lga'          => 'Kampala',
            'ad_status'    => 'active',
            'views'        => '0',
        ]);

        return Payment::create([
            'advert_id'         => $advert->id,
            'user_id'           => $buyer->user_id,
            'phone'             => '0771234567',
            'payment_reference' => 'ref_' . uniqid(),
            'amount'            => '50000',
            'commission'        => '5000',
            'amount_paid'       => '50000',
            'payment_status'    => 'paid',
            'seller_settlement' => 'no',
        ]);
    }

    public function test_bank_payout_calls_flutterwave_transfer_with_bank_type(): void
    {
        Mail::fake();
        Http::fake([
            '*/transfers' => Http::response(['status' => 'success', 'data' => ['id' => 12345]], 200),
        ]);

        $seller = User::factory()->create();
        $seller->forceFill([
            'payout_method'  => 'bank',
            'bank_code'      => '540',
            'account_number' => '1234567890',
            'account_name'   => 'Test Seller',
        ])->save();

        $payment = $this->makePayment($seller);

        app(ManagePayments::class)->sendPayout(new Request(), $payment->id);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/transfers')
                && $request['type'] === 'account'
                && $request['account_bank'] === '540'
                && $request['account_number'] === '1234567890'
                && $request['beneficiary_name'] === 'Test Seller'
                && $request['currency'] === 'UGX';
        });

        $this->assertDatabaseHas('payments', [
            'id'                 => $payment->id,
            'seller_settlement'  => 'yes',
        ]);
    }

    public function test_mobile_money_payout_calls_flutterwave_transfer_with_mobile_type(): void
    {
        Mail::fake();
        Http::fake([
            '*/transfers' => Http::response(['status' => 'success', 'data' => ['id' => 12346]], 200),
        ]);

        $seller = User::factory()->create();
        $seller->forceFill([
            'payout_method'       => 'mobile_money',
            'mobile_network'      => 'MTN',
            'mobile_money_number' => '0771234567',
        ])->save();

        $payment = $this->makePayment($seller);

        app(ManagePayments::class)->sendPayout(new Request(), $payment->id);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/transfers')
                && $request['type'] === 'mobilemoneyuganda'
                && $request['account_number'] === '0771234567'
                && $request['network'] === 'MTN'
                && $request['currency'] === 'UGX';
        });

        $this->assertDatabaseHas('payments', [
            'id'                 => $payment->id,
            'seller_settlement'  => 'yes',
        ]);
    }

    public function test_bank_payout_fails_gracefully_when_bank_details_incomplete(): void
    {
        Http::fake();

        $seller = User::factory()->create();
        // payout_method defaults to bank branch; no bank_code/account_number set.

        $payment = $this->makePayment($seller);

        app(ManagePayments::class)->sendPayout(new Request(), $payment->id);

        Http::assertNothingSent();

        $this->assertDatabaseHas('payments', [
            'id'                 => $payment->id,
            'seller_settlement'  => 'no',
        ]);
    }
}
