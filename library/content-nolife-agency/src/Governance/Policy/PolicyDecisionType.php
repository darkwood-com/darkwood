<?php

namespace App\Governance\Policy;

enum PolicyDecisionType: string
{
    case Pass = 'pass';
    case Reject = 'reject';
    case RequireApproval = 'require_approval';
}
