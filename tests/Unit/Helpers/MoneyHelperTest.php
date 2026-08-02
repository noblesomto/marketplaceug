<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class MoneyHelperTest extends TestCase
{
    public function test_money_formats_whole_number_with_ugx_prefix(): void
    {
        $this->assertSame('UGX 150,000', money(150000));
    }

    public function test_money_formats_with_decimals(): void
    {
        $this->assertSame('UGX 150,000.50', money(150000.5, 2));
    }

    public function test_money_handles_zero(): void
    {
        $this->assertSame('UGX 0', money(0));
    }

    public function test_money_handles_string_numeric_input(): void
    {
        $this->assertSame('UGX 1,200', money('1200'));
    }
}
