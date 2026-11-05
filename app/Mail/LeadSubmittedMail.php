<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells an affiliate that a lead from their link is in Pipedrive. Subject and body
 * come already filled from the template under Integrations → Email.
 */
class LeadSubmittedMail extends Mailable
{
    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public ?string $replyToAddress = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress)] : [],
        );
    }

    public function content(): Content
    {
        // Blank lines split paragraphs; single line breaks are kept inside them
        $paragraphs = array_values(array_filter(
            preg_split("/\R\s*\R/", trim(str_replace("\r\n", "\n", $this->bodyText))),
            fn($p) => trim($p) !== ''
        ));

        return new Content(
            view: 'emails.lead-submitted',
            text: 'emails.lead-submitted-text',
            with: ['paragraphs' => $paragraphs],
        );
    }
}
