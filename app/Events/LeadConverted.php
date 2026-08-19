<?php

namespace App\Events;

use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadConverted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lead $lead;
    public Opportunity $opportunity;

    public function __construct(Lead $lead, Opportunity $opportunity)
    {
        $this->lead = $lead;
        $this->opportunity = $opportunity;
    }
}
