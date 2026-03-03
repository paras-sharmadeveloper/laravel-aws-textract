<?php

namespace App\Services;

use OpenAI;
use Illuminate\Support\Facades\Log;

class GPTService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    public function parse(array $payload)
    {
        $prompt = $this->buildPrompt($payload);

        try {

            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'temperature' => 0,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

            $content = $response->choices[0]->message->content ?? '';

            // Clean accidental markdown
            $content = trim($content);
            $content = preg_replace('/^```json/', '', $content);
            $content = preg_replace('/```$/', '', $content);

            $decoded = json_decode($content, true);

            if (!$decoded) {
                Log::error("GPT Invalid JSON", ['response' => $content]);
                return [];
            }

            return $decoded;

        } catch (\Exception $e) {

            Log::error("GPT Error", [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    private function buildPrompt(array $payload)
    {
        $driverOCR = $payload['documents']['driver_license']['raw_text'] ?? '';
        $bankOCR   = $payload['documents']['bank_document']['raw_text'] ?? '';
        $taxOCR    = $payload['documents']['tax_document']['raw_text'] ?? '';
        $otherOCR  = $payload['documents']['other_document']['raw_text'] ?? '';
        $email     = $payload['email'] ?? '';
        $phone     = $payload['phone'] ?? '';
        return "<<<PROMPT
            You are an expert KYC and business onboarding data extractor.

            Output ONLY valid JSON.
            Output must start with { and end with }.
            ALL keys must exist. Use null if missing.

            OUTPUT JSON KEYS:
            first_name
            last_name
            email
            phone
            date_of_birth
            license_number
            home_address
            bank_name
            routing_number
            account_number
            business_name
            business_address
            business_owner
            fns_number
            sales_rep_name
            identification_number
            tax_id
            files
            confidence_score

            Email: {$email}
            Phone: {$phone}

            Driver License OCR:
            {$driverOCR}

            Bank Document OCR:
            {$bankOCR}

            Tax Document OCR:
            {$taxOCR}

            Other Document OCR:
            {$otherOCR}
            PROMPT";
    }
}
