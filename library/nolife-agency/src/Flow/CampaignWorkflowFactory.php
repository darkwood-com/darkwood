<?php

namespace App\Flow;

final readonly class CampaignWorkflowFactory
{
    public function __construct(
        private AgencyWorkflowFactory $agencyWorkflowFactory,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        return $this->agencyWorkflowFactory->campaignDelivery();
    }
}
