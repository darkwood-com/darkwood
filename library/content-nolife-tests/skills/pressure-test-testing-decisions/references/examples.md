# Companion repo map

Use these paths when the user is inside `darkwood/nolife-tests`.

## Bad — false insurance (pressure-test candidates for delete/rewrite)

| File | Typical decision | Opening probe |
| --- | --- | --- |
| `tests/Bad/ImplementationCoupledTest.php` | Delete or rewrite toward Job/workflow oracles | "If a broken real `ParseJob` shipped, would this stay green?" |
| `tests/Bad/RuntimeCoupledTest.php` | Delete from the app suite | "Is `await()` count product behavior or driver furniture?" |
| `tests/Bad/CoverageTheaterTest.php` | Rewrite assertions to title/excerpt meaning | "Could a wrong excerpt still pass?" |

## Good — behavioral oracles (pressure-test candidates for keep / strengthen)

| File | Typical decision | Opening probe |
| --- | --- | --- |
| `tests/Good/JobBehaviorTest.php` | Keep; optionally extend cases | "Does each case name Job I/O meaning without Flow?" |
| `tests/Good/WorkflowBehaviorTest.php` | Keep; guard `FlowCollector` honesty | "Does the oracle fail on thin docs and wrong titles?" |
| `tests/Good/MultiDriverBehaviorTest.php` | Keep as the punch-line test | "Do Fiber and Amp share identical assertions?" |

## Ports vs drivers

| Do | Don't |
| --- | --- |
| Stub `App\Port\DocumentSource` / use `InMemoryDocumentSource` | Mock `Flow\DriverInterface` in app tests |
| Unit-test `App\Job\*` transforms | Assert mock call order across the job chain |
| Run `composer test:good` as the real suite | Treat `composer test:bad` as insurance |

## Suggested prompts

```text
Use $pressure-test-testing-decisions on tests/Bad/ImplementationCoupledTest.php —
should we keep, rewrite, or delete it?
```

```text
Use $pressure-test-testing-decisions: our agent added a test that counts await()
calls. Grill me until I decide what to do.
```

```text
Use $pressure-test-testing-decisions: is stubbing DocumentSource enough, or should
we mock FetchJob too?
```
