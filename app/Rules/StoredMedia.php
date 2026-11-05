<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * A path to a file that was uploaded through the admin panel.
 */
class StoredMedia implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isValid = is_string($value)
            && ! Str::contains($value, ['..', '\\'])
            && ! Str::startsWith($value, '/')
            && Storage::disk(config('cms.disk'))->exists($value);

        if (! $isValid) {
            $fail('This file could not be found. Please upload it again.');
        }
    }
}
