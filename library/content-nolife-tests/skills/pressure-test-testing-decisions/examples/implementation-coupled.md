# Example session — ImplementationCoupledTest

Annotated transcript. Not a script to paste blindly; shows closure shape.

## Situation

Reviewer finds `tests/Bad/ImplementationCoupledTest.php` green. It mocks every Job and asserts call order. Article context: agents treat green as merge permission.

## Condensed grill

**Agent:** Which testing decision are we making, and by when?

**User:** Keep, rewrite, or delete `ImplementationCoupledTest` before the article ships.

**Agent:** If a broken real `ParseJob` returned garbage HTML meaning, would this test fail?

**User:** No — mocks never call the real ParseJob.

**Agent:** So the insurance claim is call order under doubles, not excerpt meaning. Options: keep as a teaching Bad example only, rewrite into `JobBehaviorTest`-style oracles, or delete from any suite that gates merges. Which downside is worse—losing a "workflow wiring" check, or teaching agents that order mocks are enough?

**User:** Losing wiring checks is fine; we have `WorkflowBehaviorTest`. False insurance is worse.

**Agent:** Next action?

**User:** Keep the file under `tests/Bad/` for the article; exclude it from any required CI job that means "product insurance." Document that in the README.

## Decision record

| Field | Entry |
| --- | --- |
| Decision | keep (educational Bad only) / never gate merges on it |
| Test or choice under review | `tests/Bad/ImplementationCoupledTest.php` |
| Why now | Article + agent green-light risk |
| Options considered | keep-as-insurance; rewrite to behavioral; delete; keep-as-Bad-only |
| What the test asserts today | Mocked jobs called in order with canned returns |
| Product bug that would stay green | Broken real `ParseJob` / wrong excerpt meaning |
| Critical assumptions | Call order ≈ integration; green ≈ safe to merge |
| Main trade-off | Teaching example vs false CI confidence |
| Confidence | 0.85 |
| What would change the decision | Introducing a real end-to-end check that only this file provides (it does not) |
| Failure warning / tripwire | If CI required suite includes Bad, split configs immediately |
| Next action | Confirm `composer test:good` is the required job; README already labels Bad |
| Owner | Author |
| Deadline | Before publishing the article |
| Review date | After first agent PR that cites this test as coverage |
