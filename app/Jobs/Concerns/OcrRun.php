<?php

namespace App\Jobs\Concerns;

/**
 * Shared helpers for the parallel OCR pipeline: cache keys for per-file
 * results and the text clean-up that ProcessOcrJob has always applied.
 */
trait OcrRun
{
    protected static function documentKey(string $runId, string $docName): string
    {
        return "ocr:{$runId}:doc:" . md5($docName);
    }

    protected static function statementKey(string $runId, int|string $idx): string
    {
        return "ocr:{$runId}:stmt:{$idx}";
    }

    protected function cleanRawText($text)
    {
        // Remove control characters (MOST IMPORTANT)
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text);

        // Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        return $text;
    }
}
