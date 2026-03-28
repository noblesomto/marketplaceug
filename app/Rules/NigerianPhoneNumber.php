<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NigerianPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Only accept exactly 11 digits starting with 0 — no spaces, dashes, or +234
        return (bool) preg_match('/^0(70|80|81|90|91)[0-9]{8}$/', $value);
    }

    public function message()
    {
        return 'Please enter a valid Nigerian phone number in the format 08012345678 (11 digits, starting with 0).';
    }
}
