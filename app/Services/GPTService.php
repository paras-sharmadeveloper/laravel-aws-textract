<?php

namespace App\Services;

use OpenAI;
use Illuminate\Support\Facades\Log;

class GPTService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    public function parse(array $payload)
    {

        // \Log::error("Payload OCr GPT", ['content' => $payload]);
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
            //\Log::error("decoded GPT", ['content' => $decoded]);

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

            Your task is to analyze ALL provided OCR text together and extract a SINGLE unified JSON object.

            STRICT OUTPUT RULES (MANDATORY):

            Output ONLY valid JSON

            Do NOT add explanations

            Do NOT add markdown

            Do NOT wrap in ```json

            Output must start with {{ and end with }}

            ALL output keys must exist (use null if missing)

            If a value is unclear, conflicting, or incomplete, return null

            DATA RULES:

            Use semantic understanding, not keyword guessing

            Do NOT hallucinate values

            Clean OCR noise, watermarks, headers, footers, and duplicates

            Normalize formatting (dates, numbers, spacing)

            Prefer the most complete and reliable value when duplicates exist

            DRIVER LICENSE NAME RULES:

            Extract the PERSON’S name only

            Ignore government authority text, watermarks, and slogans such as:
            'NEW YORK STATE', 'USA', 'NOT FOR FEDERAL PURPOSES',
            'DRIVER LICENSE', 'SEAL', 'EXCELSIOR',
            names of commissioners or officials

            Ignore repeated, partial, or misspelled name fragments caused by OCR errors

            The valid person name usually appears near the address and date of birth

            Split name strictly into first_name and last_name

            Do NOT guess missing name parts

            SOURCE OF TRUTH (PRIORITY ORDER):

            Driver License →
            first_name, last_name, date_of_birth, license_number, home_address

            Tax Document →
            tax_id, identification_number, business_name, business_address, business_owner

            Bank Document →
            bank_name, routing_number, account_number

            If the same field appears in multiple documents:

            Use the value from the higher-priority document

            Ignore conflicting values from lower-priority documents

            ADDRESS RULES:
            If address appears across multiple consecutive lines,
            merge them into one full address string.

            If a structured address is present in ID extracted data,
            use it instead of OCR text.

            Return ONLY one single full address string for home_address

            Combine street, city, state, and ZIP if present

            Do NOT split address into parts

            Do NOT infer or guess missing address elements

            FILES RULE:

            'files' must be an array of document types detected from the OCR text

            Allowed values:
            'driver_license'
            'bank_document'
            'tax_document'

            Include only documents that clearly appear in the input

            CONFIDENCE SCORE RULES:

            confidence_score must be a number between 0 and 100

            Score reflects overall confidence in extracted identity data

            Reduce score if OCR is noisy, values required normalization, or conflicts existed

            Use null only if confidence cannot be reasonably estimated

            OUTPUT JSON KEYS (EXACT, DO NOT CHANGE):

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
