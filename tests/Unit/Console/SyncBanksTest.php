<?php

namespace Tests\Unit\Console;

use App\Models\Bank;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncBanksTest extends TestCase
{
    use DatabaseTransactions;

    public function test_syncs_banks_from_flutterwave_response(): void
    {
        config(['services.flutterwave.secretKey' => 'test-secret']);
        config(['services.flutterwave.paymentUrl' => 'https://api.flutterwave.com/v3']);

        Http::fake([
            'https://api.flutterwave.com/v3/banks/UG' => Http::response([
                'status' => 'success',
                'message' => 'Banks retrieved',
                'data' => [
                    ['id' => 1, 'code' => '101', 'name' => 'Stanbic Bank Uganda'],
                    ['id' => 2, 'code' => '102', 'name' => 'Centenary Bank'],
                ],
            ], 200),
        ]);

        $this->artisan('banks:sync')->assertExitCode(0);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.flutterwave.com/v3/banks/UG'
                && $request->hasHeader('Authorization', 'Bearer test-secret');
        });

        $this->assertDatabaseHas('banks', [
            'bank_code' => '101',
            'name' => 'Stanbic Bank Uganda',
        ]);
        $this->assertDatabaseHas('banks', [
            'bank_code' => '102',
            'name' => 'Centenary Bank',
        ]);
    }

    public function test_returns_error_when_flutterwave_request_fails(): void
    {
        config(['services.flutterwave.secretKey' => 'test-secret']);
        config(['services.flutterwave.paymentUrl' => 'https://api.flutterwave.com/v3']);

        Http::fake([
            'https://api.flutterwave.com/v3/banks/UG' => Http::response([], 500),
        ]);

        $this->artisan('banks:sync')->assertExitCode(1);

        $this->assertDatabaseCount('banks', 0);
    }
}
