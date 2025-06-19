<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserIdOrAll implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === 'all') {
            return; // valid
        }

        if (!ctype_digit((string)$value) || !User::where('id', $value)->exists()) {
            $fail("The selected $attribute is invalid.");
        }
    }
}
