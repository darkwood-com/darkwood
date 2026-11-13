# Example session — stub DocumentSource or mock FetchJob?

## Situation

Teammate asks whether workflow tests should mock `FetchJob` or stub `DocumentSource`.

## Condensed grill

**Agent:** What observable outcome are you trying to protect?

**User:** Faithful title + excerpt from HTML at a URI.

**Agent:** Where is the I/O boundary?

**User:** `DocumentSource`.

**Agent:** If you mock `FetchJob`, what real code stops running?

**User:** FetchJob itself — and we lose confidence it maps URI → RawDocument correctly.

**Agent:** Stub-at-port vs mock-chain: which keeps Jobs real while controlling the network?

**User:** `InMemoryDocumentSource`.

## Decision record

| Field | Entry |
| --- | --- |
| Decision | stub `DocumentSource`; do not mock `FetchJob` / driver |
| Test or choice under review | Workflow test doubles strategy |
| Why now | Agent suggested mocking the whole chain |
| Options considered | mock all Jobs; stub port; hit real HTTP |
| What the test asserts today | (planned) title + excerpt meaning |
| Product bug that would stay green | Under full Job mocks: any broken Job |
| Critical assumptions | Port fake is faithful enough for HTML fixtures |
| Main trade-off | Slightly wider unit vs false isolation |
| Confidence | 0.88 |
| What would change the decision | FetchJob grows complex branching worth its own `JobBehaviorTest` cases |
| Failure warning / tripwire | New mocks of `JobInterface` in `tests/Good/` |
| Next action | Open `WorkflowBehaviorTest` and confirm it already uses `InMemoryDocumentSource` |
| Owner | Author |
| Deadline | Today |
| Review date | Next workflow change |
