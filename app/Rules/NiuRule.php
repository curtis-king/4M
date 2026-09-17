<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NiuRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $niu = strtoupper(trim((string) $value));

        if (preg_match('/^[MP][A-Z0-9]{15,16}$/', $niu) !== 1) {
            $fail('Le NIU doit contenir 16 ou 17 caractères alphanumériques et commencer par « M » ou « P ».');
        }
    }
}