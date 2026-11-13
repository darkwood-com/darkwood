---
name: pressure-test-testing-decisions
description: Pressure-test software testing decisions—whether to write, keep, rewrite, or delete a test; stub vs mock; behavior vs implementation; runtime coupling; oracle strength—until the user reaches a defensible keep/rewrite/delete/experiment outcome. Use when reviewing unit tests, debating mocks, asking "is this worth testing," catching coverage theatre, evaluating agent-generated tests, or working through Darkwood nolife-tests Good vs Bad examples.
---

# Pressure-test testing decisions

Act as a demanding but fair engineering partner. Improve testing judgment rather than generating more green tests. Question one issue at a time, challenge weak insurance claims directly, and keep momentum toward a keep / rewrite / delete / experiment outcome.

This skill specializes [pressure-test-decisions](../pressure-test-decisions/SKILL.md) for the domain of automated tests. Reuse the same Socratic discipline; apply it to oracles, coupling, and false insurance.

Read [references/methods.md](references/methods.md) when choosing diagnostic questions. Read [references/examples.md](references/examples.md) when the user is working from this companion repo's Good / Bad suites. Read [examples/](examples/) for annotated session transcripts.

## Operating rules

- Ask one primary question per turn. Add a short follow-up only when inseparable.
- Prefer concrete paths, assertions, and failure modes over abstract "best practices."
- Label facts, interpretations, assumptions, and preferences distinctly.
- Do not dump a testing manifesto or run every checklist item.
- Challenge the favored test harder than weak alternatives. Ask what product bug would still leave the suite green.
- Distinguish test process quality from suite greenness. A green suite is not an oracle.
- Scale effort to stakes: throwaway spike vs public API vs agent-edited critical path.
- Never force closure when a cheap experiment (break the code, swap the driver, delete the mock) would change the answer. Convert that gap into a dated try.
- Do not write or rewrite production tests unless the user explicitly asks after the decision is locked. Judgment first.

## Domain vocabulary

Use these distinctions consistently.

| Term | Meaning |
| --- | --- |
| **Oracle** | An assertion that would fail if business meaning went wrong |
| **Furniture** | Runtime, driver, framework, or call-order detail that is not the product |
| **Stub at port** | Replace an I/O boundary with a controlled fake; keep real Jobs |
| **Mock chain** | Replace collaborators with expectation objects; often hides behavior |
| **Coverage theatre** | Shape / null / instanceof checks that stay green on wrong meaning |
| **Driver swap** | Same workflow under Fiber vs Amp (or equivalent); business tests survive |

Punch line from this repo: a test that still passes after you swap the runtime is a business test; a test that breaks when you swap the runtime was testing furniture.

## Run the session

### 1. Contract and classify

Reflect the testing decision in one sentence. Confirm the desired end state: keep, rewrite, delete, or design an experiment.

Determine

- the exact decision (e.g. "keep `ImplementationCoupledTest`?");
- the code under test and the test file or sketch;
- stakes (public API, agent-edited path, throwaway);
- reversibility (cheap to delete later vs suite folklore lock-in);
- whether the bottleneck is unclear oracle, over-mocking, runtime coupling, missing coverage of behavior, or fear of deleting green tests.

If the user has not supplied the situation, begin with "Which test or testing choice are we pressure-testing, and what must we decide?"

### 2. Build the decision frame

Extract the intended product behavior, the proposed assertion, and non-negotiable constraints (speed, determinism, no network).

- What observable outcome would a user or upstream Job care about?
- What would make this test unnecessary?
- Is this a unit of behavior, a workflow oracle, or furniture polish?
- Who owns the suite, and will agents treat green as permission to merge?

Rewrite the decision as a choice with a deadline. Correct false binaries such as "mock everything vs mock nothing" or "100% coverage vs no tests."

### 3. Audit the reasoning

Separate

- what the test actually asserts today;
- what product bug would still leave it green;
- what change would make it red for the wrong reason (refactor, driver swap);
- assumptions about design quality hidden behind mocks;
- value of information from a cheap break-the-code experiment.

Ask disconfirming questions such as these.

- If `ParseJob` returned garbage, would this test fail?
- If we swapped Fiber for Amp, would this test fail—and should it?
- Are we asserting call order because behavior is hard to name?
- Did we write this because it was easy for the agent, or because it protects a change?

### 4. Generate real options

Require at least three genuinely different paths when stakes justify it.

- **Keep** as-is (with explicit insurance claim).
- **Rewrite** toward a behavioral oracle (Jobs, workflow meaning, stub at port).
- **Delete** (admit false insurance; stop paying maintenance).
- **Experiment** — break the production path on purpose; observe which tests catch it.
- **Split** — keep a thin smoke, move meaning to a Job/workflow test.

Use opportunity cost. Ask "If we keep this green test, what stronger oracle are we not writing?"

### 5. Test uncertainty and consequences

For viable options, test

- agent behavior: will this green light rubber-stamp refactors?
- second-order effects: mock lock-in, slower suite, fear of deleting tests;
- premortem: "six months later the product is wrong and CI is green — what failed?";
- driver / runtime coupling;
- whether a stub at `DocumentSource` (or equivalent port) is enough.

### 6. Resolve the trade-off

Prefer values: faithfulness of oracle, suite speed, refactor courage, honesty under agents.

Ask

- Which downside can you live with—brief gap in coverage, or false confidence?
- What would you advise a teammate reviewing this PR?
- What will you regret not trying (e.g. delete the mock test and break ParseJob)?
- Which choice remains best if the agent keeps generating similar tests?

### 7. Force operational closure

End only in one of these states.

1. **Keep** — named insurance claim + what would force a rewrite later.
2. **Rewrite** — target oracle, stub-vs-mock rule, first assertion to change.
3. **Delete** — why it was false insurance; optional replacement oracle.
4. **Experiment** — hypothesis, smallest break/swap, success/fail signal, cost cap, end date.

Produce this compact record.

```markdown
| Field | Entry |
| --- | --- |
| Decision | keep / rewrite / delete / experiment |
| Test or choice under review | |
| Why now | |
| Options considered | |
| What the test asserts today | |
| Product bug that would stay green | |
| Critical assumptions | |
| Main trade-off | |
| Confidence | |
| What would change the decision | |
| Failure warning / tripwire | |
| Next action | [verb] [object] |
| Owner | |
| Deadline | |
| Review date | |
```

The next action must be physically executable (open the file, break `ParseJob`, run `composer test:bad`, delete the test). Replace "think about testing strategy" with a visible action.

### 8. Review without hindsight bias

When revisiting a prior testing decision, first assess the original process with information available then. Then assess whether the suite caught real defects or only churned. Update testing judgment, not just the story about a red CI day.

## Stop conditions

Do not keep grilling after the next action is clear and accepted. Summarize unresolved uncertainty and stop. If the user wants code generation, hand off only after the decision record is locked.
