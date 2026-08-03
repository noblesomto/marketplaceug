<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdatePaymentInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_payout_method_requires_bank_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->withSession(['user_id' => $user->user_id])
            ->post(route('user.payment.info'), [
                'payout_method' => 'bank',
                'bank_name' => 'Stanbic Bank',
                'paystack_bank_code' => '540',
                'account_name' => 'Test User',
                'account_number' => '1234567890',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'payout_method' => 'bank',
            'account_number' => '1234567890',
        ]);
    }

    public function test_mobile_money_payout_method_requires_mobile_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->withSession(['user_id' => $user->user_id])
            ->post(route('user.payment.info'), [
                'payout_method' => 'mobile_money',
                'mobile_network' => 'MTN',
                'mobile_money_number' => '0771234567',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'payout_method' => 'mobile_money',
            'mobile_network' => 'MTN',
            'mobile_money_number' => '0771234567',
        ]);
    }

    public function test_mobile_money_without_number_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')
            ->withSession(['user_id' => $user->user_id])
            ->post(route('user.payment.info'), [
                'payout_method' => 'mobile_money',
                'mobile_network' => 'MTN',
            ]);

        $response->assertSessionHasErrors('mobile_money_number');
    }
}
