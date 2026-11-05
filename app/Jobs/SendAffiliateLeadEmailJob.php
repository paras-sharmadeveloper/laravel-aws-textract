<?php

namespace App\Jobs;

/**
 * @deprecated Use SendLeadEmailJob. Kept so jobs queued before the rename still run.
 */
class SendAffiliateLeadEmailJob extends SendLeadEmailJob
{
    public function __construct(int $leadId)
    {
        parent::__construct($leadId, self::AFFILIATE);
    }
}
