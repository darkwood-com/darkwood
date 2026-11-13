<?php

namespace App\Governance\Audit;

enum AuditEventType: string
{
    case AgentRunRequested = 'agent.run.requested';
    case AgentRunStarted = 'agent.run.started';
    case AgentRunCompleted = 'agent.run.completed';
    case AgentRunFailed = 'agent.run.failed';
    case PolicyCheckStarted = 'policy.check.started';
    case PolicyCheckPassed = 'policy.check.passed';
    case PolicyCheckFailed = 'policy.check.failed';
    case ApprovalRequested = 'approval.requested';
    case ApprovalGranted = 'approval.granted';
    case ApprovalRejected = 'approval.rejected';
    case TraceStarted = 'trace.started';
    case TraceAttached = 'trace.attached';
    case TraceFailed = 'trace.failed';
    case ToolDeclared = 'tool.declared';
    case ToolInvoked = 'tool.invoked';
    case ToolCompleted = 'tool.completed';
    case ToolFailed = 'tool.failed';
    case ArtifactCreated = 'artifact.created';
    case ArtifactAttached = 'artifact.attached';
    case WorkflowStarted = 'workflow.started';
    case WorkflowStateChanged = 'workflow.state.changed';
    case DeliveryReviewed = 'delivery.reviewed';
    case HumanApprovalRequired = 'human.approval.required';
    case GovernanceViolation = 'governance.violation';
}
