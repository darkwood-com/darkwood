<?php

namespace App\Flow;

final readonly class DeliveryWorkflowFactory
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
