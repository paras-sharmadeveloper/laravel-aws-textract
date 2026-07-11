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
     */
    public function extractPeriod(string $text): ?array
    {
        // "Statement Period: MM/DD/YYYY - MM/DD/YYYY" -> use the period end date
        if (preg_match(
            '/Statement\s+Period\s*:?\s*(\d{1,2})\/(\d{1,2})\/(\d{4})\s*-\s*(\d{1,2})\/(\d{1,2})\/(\d{4})/i',
            $text,
            $m
        )) {
            return $this->format((int) $m[4], (int) $m[6]);
        }

        // "Statement Date: MM/DD/YYYY"
        if (preg_match(
            '/Statement\s+Date\s*:?\s*(\d{1,2})\/(\d{1,2})\/(\d{4})/i',
            $text,
            $m
        )) {
            return $this->format((int) $m[1], (int) $m[3]);
        }

        return null;
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
