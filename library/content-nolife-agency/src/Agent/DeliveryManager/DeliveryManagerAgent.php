<?php

namespace App\Agent\DeliveryManager;

use App\Agent\AbstractAgent;

final class DeliveryManagerAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'delivery_manager';
    }

    public function role(): string
    {
        return 'Governance, QA, approval and delivery coordinator';
    }
}
