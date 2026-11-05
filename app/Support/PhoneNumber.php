<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Build the tap-to-call link from a displayed US phone number.
     *
     * "(614) 483-0331" becomes "tel:+16144830331".
     */
    public static function href(string $display): string
    {
        $digits = preg_replace('/\D/', '', $display) ?? '';

        if (strlen($digits) === 10) {
            $digits = '1'.$digits;
        }

        return 'tel:+'.$digits;
    }

    /**
     * Count the digits in a phone number.
     */
    public static function digitCount(string $phone): int
    {
        return strlen(preg_replace('/\D/', '', $phone) ?? '');
    }
}
