<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UgandanPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^07[0-9]{8}$/', (string) $value)) {
            $fail('The :attribute must be a valid Ugandan mobile number (e.g. 07XXXXXXXX).');
        }
    }
}
