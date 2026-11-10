# Example session — RuntimeCoupledTest

## Situation

`tests/Bad/RuntimeCoupledTest.php` asserts something about the driver / `await()` furniture. Question: should app suites pin the event-loop wrapper?

## Condensed grill

**Agent:** Is `await()` count product behavior or runtime furniture?

**User:** Furniture. We care about excerpt title and text.

**Agent:** Would Fiber→Amp break this test even when the excerpt is correct?

**User:** Yes.

**Agent:** Premortem: suite green, product wrong — what failed? Or the opposite: product right, suite red after a driver upgrade — what failed?

**User:** The second one. We'd block a driver bump for no product reason.

**Agent:** Options: delete from app suite; move smoke to Flow's own package CI; keep as Bad teaching example.

## Decision record

| Field | Entry |
| --- | --- |
| Decision | delete from product insurance; keep under `tests/Bad/` as article foil |
| Test or choice under review | `tests/Bad/RuntimeCoupledTest.php` |
| Why now | Multi-driver story in the article |
| Options considered | keep; rewrite without await spies; delete; Bad-only |
| What the test asserts today | Runtime wrapper interaction |
| Product bug that would stay green | Wrong title/excerpt with correct await count |
| Critical assumptions | Driver API stability is the app's job |
| Main trade-off | Catching driver misuse vs coupling to furniture |
| Confidence | 0.9 |
| What would change the decision | Product requirement to support only one driver and pin its API in-app (unlikely) |
| Failure warning / tripwire | Any new test that imports `DriverInterface` mocks in `tests/Good/` |
| Next action | Point article §5 at `MultiDriverBehaviorTest` as the replacement pattern |
| Owner | Author |
| Deadline | Outline draft |
| Review date | When ReactPHP / Spatie drivers appear in demos |
