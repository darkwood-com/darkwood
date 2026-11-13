<?php

namespace App\Integration\Flow;

interface FlowWorkflowClient
{
    public function name(): string;

    public function dispatch(WorkflowDispatch $dispatch): WorkflowResult;
}
