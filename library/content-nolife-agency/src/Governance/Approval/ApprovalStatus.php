<?php

namespace App\Governance\Approval;

enum ApprovalStatus: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
