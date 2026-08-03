<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotForbiddenName implements Rule
{
    protected $forbiddenNames = [
        'market',
        'marketplace',
        'ug',
        'marketplace uganda'
    ];

    public function passes($attribute, $value)
    {
        return !in_array(strtolower($value), $this->forbiddenNames);
    }

    public function message()
    {
        return 'This name is not allowed.';
    }
}
