<?php

namespace App\Support;

/**
 * Formats phone numbers as US numbers: "+1 (646) 825-4477" for display and
 * "+16468254477" for tel: links. Anything that isn't a 10-digit US number
 * (or 11 digits starting with 1) is left as it was entered.
 */
class Phone
{
    public const COMPANY = '6468254477';

    public static function display(?string $phone): string
    {
        $digits = self::usDigits($phone);

        return $digits === null
            ? (string) $phone
            : sprintf('+1 (%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
    }

    public static function tel(?string $phone): string
    {
        $digits = self::usDigits($phone);

        return $digits === null ? preg_replace('/[^0-9+]/', '', (string) $phone) : '+1' . $digits;
    }

    private static function usDigits(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }

        return strlen($digits) === 10 ? $digits : null;
    }
}
