<?php

namespace App\Agent\Runtime;

enum AgentExecutionStatus: string
{
    case Requested = 'requested';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
