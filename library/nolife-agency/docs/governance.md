# Governance

Governance enforcement stays in `App\Service\AgentRunner`.

Key guarantees:

- pre-flight and post-flight policy checks
- approval enforcement for high-risk/final delivery states
- workflow-state safety checks
- artifact path containment under run directory
- trace lifecycle correlation with audit events

Audit logs are written as JSONL:

- `var/audit/agent-runs/{run_id}.jsonl`

Trace files are persisted per run:

- `var/nolife-agency/runs/{run_id}/trace.json`

Adapters do not perform governance decisions.
