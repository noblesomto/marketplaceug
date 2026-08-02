<?php

namespace Tests\Unit\Models;

use App\Models\Lga;
use App\Models\State;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LgaTest extends TestCase
{
    use DatabaseTransactions;

    protected $state;

    protected function setUp(): void
    {
        parent::setUp();
        // Use existing Uganda state data (seeded in previous tasks) or get first available
        $this->state = State::first();
        if (!$this->state) {
            $this->markTestSkipped('No states found in database. Please ensure StateSeeder has run.');
        }
    }

    public function test_shipping_fee_is_fillable_and_defaults_to_zero(): void
    {
        $district = Lga::create([
            'state_id' => $this->state->id,
            'name' => 'Test District ' . uniqid(),
            'slug' => 'test-district-' . uniqid(),
        ]);

        $this->assertEquals(0, $district->fresh()->shipping_fee);
    }

    public function test_shipping_fee_can_be_set(): void
    {
        $district = Lga::create([
            'state_id' => $this->state->id,
            'name' => 'Test District ' . uniqid(),
            'slug' => 'test-district-' . uniqid(),
            'shipping_fee' => 15000,
        ]);

        $this->assertEquals(15000, $district->fresh()->shipping_fee);
    }
}
