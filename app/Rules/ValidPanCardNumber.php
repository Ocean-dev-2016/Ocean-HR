<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class ValidPanCardNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Remove spaces/dashes
        $value = preg_replace('/[\s-]+/', '', $value);
        
        return preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid PAN number (e.g., ABCDE 1234 F).';
    }
}
