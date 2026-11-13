# Governance and Audit

`AgentRunner` is the single enforcement point for:

- policy checks
- trace lifecycle
- audit event emission
- approval requirements

Audit events are persisted as JSONL per run:

`var/audit/agent-runs/{run_id}.jsonl`

Trace output for demo/runtime:

`var/nolife-agency/runs/{run_id}/trace.json`
