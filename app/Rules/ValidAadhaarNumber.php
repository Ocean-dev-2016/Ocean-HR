<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class ValidAadhaarNumber implements Rule
{
    /**
     * Check if the Aadhaar number passes validation.
     */
    public function passes($attribute, $value)
    {
        // Remove spaces/dashes
        $value = preg_replace('/\D/', '', $value);

        // Aadhaar must be exactly 12 digits
        return preg_match('/^[0-9]{12}$/', $value);
    }

    /**
     * Validation error message.
     */
    public function message()
    {
        return 'The aadhar card number must be a valid 12-digit Aadhaar number.';
    }
}
