<?php

namespace Tests\Unit\Rules;

use App\Rules\UgandanPhoneNumber;
use Tests\TestCase;

class UgandanPhoneNumberTest extends TestCase
{
    /** @dataProvider validNumbers */
    public function test_passes_valid_ugandan_numbers(string $number): void
    {
        $rule = new UgandanPhoneNumber();
        $failed = false;
        $rule->validate('phone', $number, function () use (&$failed) { $failed = true; });
        $this->assertFalse($failed);
    }

    public static function validNumbers(): array
    {
        return [
            ['0700123456'],
            ['0771234567'],
            ['0751234567'],
        ];
    }

    /** @dataProvider invalidNumbers */
    public function test_fails_invalid_numbers(string $number): void
    {
        $rule = new UgandanPhoneNumber();
        $failed = false;
        $rule->validate('phone', $number, function () use (&$failed) { $failed = true; });
        $this->assertTrue($failed);
    }

    public static function invalidNumbers(): array
    {
        return [
            ['08034814561'],   // wrong prefix (not a valid Ugandan format)
            ['070012345'],     // too short
            ['07001234567'],   // too long
            ['1234567890'],    // no leading 0
        ];
    }
}
