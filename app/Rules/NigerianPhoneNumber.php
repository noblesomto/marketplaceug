<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NigerianPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Clean input - remove spaces, dashes, and other non-digits (except +)
        $phone = preg_replace('/[^\d+]/', '', $value);

        // Nigerian phone number patterns
        $patterns = [
            '/^(\+234|0)(70|80|81|90|91)[0-9]{8}$/',  // Most common prefixes
            '/^(\+234|0)(70[0-9]|80[2-9]|81[0-9]|90[1-9]|91[0-9])[0-9]{7}$/'  // More specific
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Please enter a valid Nigerian phone number (e.g., 08012345678 or +2348012345678).';
    }
}
