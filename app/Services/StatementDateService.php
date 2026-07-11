<?php

namespace App\Services;

class StatementDateService
{
    private const MONTHS = [
        1 => 'january',
        2 => 'february',
        3 => 'march',
        4 => 'april',
        5 => 'may',
        6 => 'june',
        7 => 'july',
        8 => 'august',
        9 => 'september',
        10 => 'october',
        11 => 'november',
        12 => 'december',
    ];

    /**
     * Extract the statement month/year from OCR'd document text.
     * Returns ['month' => 'april', 'year' => '2025'] or null if not confidently found.
     *
     * Real statements vary in ways the "obvious" format doesn't cover:
     * - 2-digit years (4/30/26)
     * - label glued to the value with no space ("StatementPeriod")
     * - other text (account numbers) between the label and the date
     * - "thru" instead of "-" as the range separator
     */
    public function extractPeriod(string $text): ?array
    {
        // "Statement Period" ... MM/DD/YY(YY) <sep> MM/DD/YY(YY) -> use the period end date
        if (preg_match(
            '/Statement\s*Period.{0,80}?(\d{1,2}\/\d{1,2}\/\d{2,4})\s*(?:-|–|—|thru|through|to)\s*(\d{1,2}\/\d{1,2}\/\d{2,4})/i',
            $text,
            $m
        )) {
            return $this->parseDate($m[2]);
        }

        // "Statement Date" ... MM/DD/YY(YY)
        if (preg_match(
            '/Statement\s*Date.{0,80}?(\d{1,2}\/\d{1,2}\/\d{2,4})/i',
            $text,
            $m
        )) {
            return $this->parseDate($m[1]);
        }

        return null;
    }

    private function parseDate(string $date): ?array
    {
        $parts = array_map('intval', explode('/', $date));

        if (count($parts) !== 3) {
            return null;
        }

        [$month, , $year] = $parts;

        if ($year < 100) {
            $year += 2000;
        }

        return $this->format($month, $year);
    }

    private function format(int $month, int $year): ?array
    {
        if (!isset(self::MONTHS[$month]) || $year < 2000 || $year > 2100) {
            return null;
        }

        return [
            'month' => self::MONTHS[$month],
            'year' => (string) $year,
        ];
    }
}
