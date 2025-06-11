<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidJnecUserEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower($value);

        if (!Str::endsWith(Str::before($email, '@'), '.jnec')) {
            $fail('The email must be in the format "username.jnec@rub.edu.bt".');
            return;
        }
        if (Str::after($email, '@') !== 'rub.edu.bt') {
            $fail('The email must be a valid RUB email address.');
        }
    }
}