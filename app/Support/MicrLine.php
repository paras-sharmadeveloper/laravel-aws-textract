<?php

namespace App\Support;

/**
 * Reads the routing and account numbers from a check's MICR line in OCR text.
 *
 * Used as a fallback when GPT returns nothing for a blank voided check, where
 * the MICR line is the only place the numbers appear. OCR mangles the MICR
 * symbols (⑆ ⑈) into junk, so we look for digit groups and trust only a
 * 9-digit group that passes the ABA checksum.
 */
class MicrLine
{
    /**
     * @return array{routing_number: ?string, account_number: ?string}
     */
    public static function parse(?string $text): array
    {
        $none = ['routing_number' => null, 'account_number' => null];

        if (blank($text)) {
            return $none;
        }

        preg_match_all('/\d+/', $text, $matches, PREG_OFFSET_CAPTURE);
        $groups = $matches[0];

        foreach ($groups as $i => [$digits]) {
            if (strlen($digits) !== 9 || !self::isRoutingNumber($digits)) {
                continue;
            }

            // The account number is the next group long enough to be one;
            // the zero-padded check number sits before the routing number
            $account = null;

            foreach (array_slice($groups, $i + 1, 3) as [$next]) {
                if (strlen($next) >= 4 && strlen($next) <= 17) {
                    $account = $next;
                    break;
                }
            }

            return ['routing_number' => $digits, 'account_number' => $account];
        }

        return $none;
    }

    public static function isRoutingNumber(string $digits): bool
    {
        if (!preg_match('/^\d{9}$/', $digits) || $digits === '000000000') {
            return false;
        }

        $d = array_map('intval', str_split($digits));

        return (3 * ($d[0] + $d[3] + $d[6]) + 7 * ($d[1] + $d[4] + $d[7]) + ($d[2] + $d[5] + $d[8])) % 10 === 0;
    }
}
