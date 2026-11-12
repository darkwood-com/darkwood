# Card lifecycle policy

Every entry in `tags.json` has two independent dimensions:

- `kind` describes what the card is: `product`, `component`, `content`, `tool`, `infrastructure`, `operations`, `experiment`, or an explicitly justified `other`;
- `lifecycle.status` describes its current state: `active`, `maintenance`, `archived`, or `deprecated`.

An archived application remains a `product`; an archived library remains a `component`. Archiving never changes functional nature and never deletes canonical Git history.

## Statuses

- `active`: developed, published, or operated now. `standard` support is expected.
- `maintenance`: still useful, with changes limited to compatibility, security, and fixes. Support is normally `security` or `best-effort`.
- `archived`: retained for provenance but not expected in permanent sparse checkouts, builds, or deployments. Support is `none`.
- `deprecated`: still reachable during a transition, but discouraged. `replacement` should identify the successor when one exists.

`archivedAt` records a known archival date only. It remains `null` when the historical date cannot be established reliably.

## Materialization

Lifecycle is not a deletion command. Before removing a card from a sparse checkout, verify dependencies, local modifications, processes, web roots, systemd units, and persistent data. Production materializes only deployed applications and required operational cards. Development environments select active work plus explicit archives needed by a task.

For one-off editorial content, review for archival one to two weeks after publication. Keep it materialized while it is served, used by a build, or required by a catalogue. Logical archival, sparse unloading, artifact cleanup, and unpublishing are four separate decisions.

## LTS

The policy is intentionally lightweight:

- `standard`: normal development and compatibility work;
- `security`: security and production-critical fixes only;
- `best-effort`: no response-time commitment;
- `none`: provenance only.

Review lifecycle metadata when a card is deployed, released, replaced, or no longer used. Do not create maintenance branches solely to represent these states.
