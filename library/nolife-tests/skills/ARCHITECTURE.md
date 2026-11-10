# Skills architecture (nolife-tests)

Short architectural notes for maintainers. Human catalog: [README.md](README.md).

## Upstream model (`gmoigneu/skills`)

A skill is a directory under `skills/<kebab-name>/` discovered by the [skills](https://www.npmjs.com/package/skills) CLI via `SKILL.md` YAML frontmatter (`name`, `description`). Optional `agents/openai.yaml` supplies display metadata and default prompt. Optional `references/*.md` hold progressive-disclosure material linked from `SKILL.md`.

There is no shared runtime library. The portable unit is the skill folder itself.

## Integration choices

| Choice | Why |
| --- | --- |
| Top-level `skills/` (not only `.cursor/skills/`) | Matches upstream so `npx skills add . --list` works |
| Vendor `pressure-test-decisions` intact | Reuse Guillaume's protocol; avoid drift |
| Add `pressure-test-testing-decisions` | Article needs testing-domain prompts and repo paths without rewriting the general skill |
| Examples as markdown transcripts | Upstream has none; a teaching companion needs closure demos |
| Apache NOTICE on vendored skill | Upstream is Apache-2.0; repo root remains MIT |

## Validation

```sh
npx --yes skills@1.5.19 add . --list
```

Expect both `pressure-test-decisions` and `pressure-test-testing-decisions`.

## Proposed follow-on skills (not implemented)

Only add these if they stay one folder + `SKILL.md` + optional `references/` — same architecture, no new framework.

| Skill id | When it earns a folder |
| --- | --- |
| `pressure-test-unit-test` | Sessions that always start from a single test method become common |
| `should-i-mock-this` | Stub-vs-mock questions dominate and crowd the general testing skill |
| `behavior-vs-implementation` | Teaching workshops want a shorter, narrower prompt than full pressure-test |
| `is-this-a-regression-test` | CI archaeology / flake triage becomes a recurring agent task |

Until then, keep one specialized skill (`pressure-test-testing-decisions`) and route those questions through its method selector.