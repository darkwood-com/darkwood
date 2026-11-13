# Method selector for testing decisions

Use only methods that address the live bottleneck. Same discipline as pressure-test-decisions; domain-specific prompts.

| Bottleneck | Method | Core move | Useful prompt |
| --- | --- | --- | --- |
| Unclear what is asserted | Oracle audit | Read each assertion; name the product meaning it protects | "What user-visible or Job-level fact would make this fail?" |
| Green but worthless | Break-the-code experiment | Sabotage the real collaborator; see which tests catch it | "If ParseJob returned garbage, which tests turn red?" |
| Narrow framing (mock vs nothing) | WRAP for tests | Widen options: stub port, Job unit, workflow oracle, delete | "What option disappears if this is not 'mock the chain'?" |
| Call-order lock-in | Behavior vs implementation | Prefer output meaning over interaction sequence | "Would a correct reordering of private calls break this unfairly?" |
| Runtime coupling | Driver-swap test | Run the same oracle under a second driver | "Would Fiber→Amp break this? If yes, is that product or furniture?" |
| Coverage theatre | Shape vs meaning | Replace instanceof/non-null with faithful content checks | "Could a wrong title still leave this green?" |
| Over-mocking | Stub-at-port rule | Fake I/O boundaries; keep real Jobs | "What is the port here, and what are we mocking instead?" |
| Agent-generated volume | False-insurance premortem | Assume CI green and product wrong; reconstruct causes | "Six months later, suite green, bug in prod — which test lied?" |
| Fear of deleting tests | Kill criteria | Set observable exit conditions for worthless greens | "What evidence would make keeping this test irresponsible?" |
| Missing evidence | Value of information | Cheapest fact that flips keep vs delete | "What is the cheapest experiment that could reverse this decision?" |
| Stalled cleanup | Implementation intention | Specify delete/rewrite action, owner, and first CI run | "Which file will you change, exactly, by when?" |

## Decision triage

1. **Stakes** — throwaway spike vs shared library vs agent-edited critical path.
2. **Reversibility** — cheap to delete later vs suite folklore that blocks refactors.
3. **Urgency** — shipping today vs teaching the team / article companion.

- Low stakes, cheap reversal → delete or keep quickly; set a review trigger.
- High stakes, cheap reversal → rewrite toward an oracle; pilot on one test.
- High stakes, costly reversal (public API) → full audit + break-the-code experiment.

## Evidence ladder (testing)

Prefer this order when judging a test's value.

1. Observed: breaking real production code turns the test red for the right reason.
2. Driver swap: same assertions stay green under Fiber and Amp (or equivalent).
3. Oracle clarity: assertions name title, excerpt, validation failure — not call counts.
4. Peer review track record: similar tests caught real regressions before.
5. Coverage percentage or "the agent wrote it" alone.

## Question discipline

- Ask one question at a time until the frame is stable.
- Prefer "what would stay green if X broke?" over generic "why did you mock?"
- Point at concrete files in this repo when available (`tests/Bad/*`, `tests/Good/*`).
- Offer a tentative keep/rewrite/delete synthesis when the user is circling, then ask them to attack it.
- Increase pressure on "it increases coverage" and "mocks prove isolation."
- Reduce pressure when the remaining choice is legitimately about suite speed vs depth.
