<?php

namespace App\Workflow;

final readonly class ClientWebsiteWorkflow
{
    public function __construct(
        private WorkflowDefinitionLoader $loader,
        private WorkflowDefinitionValidator $validator,
    ) {
    }

    public function definition(): WorkflowDefinition
    {
        $definition = $this->loader->load('client-website');
        $this->validator->validate($definition);

        return $definition;
    }
}
