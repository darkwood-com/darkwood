# Agent skills

This companion repo vendors [Guillaume Moigneu's](https://github.com/gmoigneu/skills) Agent Skills layout and adds one article-specific skill.

Skills teach coding agents how to **pressure-test decisions** — including testing decisions — instead of emitting more green coverage theatre.

## Available skills

### [Pressure-test decisions](pressure-test-decisions/)

Upstream skill from [gmoigneu/skills](https://github.com/gmoigneu/skills) (Apache-2.0). General Socratic decision protocol: frame → audit → options → uncertainty → closure table.

Vendored **without changing the decision protocol**. See `pressure-test-decisions/NOTICE`.

```sh
# From upstream
npx skills add gmoigneu/skills --skill pressure-test-decisions

# Or discover skills in this repo
npx skills add . --list
```

### [Pressure-test testing decisions](pressure-test-testing-decisions/)

Darkwood specialization for this article. Same operating discipline; domain is keep / rewrite / delete / experiment on tests, mocks, oracles, and runtime coupling. Points at `tests/Good/` and `tests/Bad/`.

```sh
npx skills add . --skill pressure-test-testing-decisions
```

## Try it against this repo

```text
Use $pressure-test-testing-decisions on tests/Bad/ImplementationCoupledTest.php —
should we keep, rewrite, or delete it?
```

```text
Use $pressure-test-testing-decisions: should we stub DocumentSource or mock FetchJob?
```

```text
Use $pressure-test-decisions to pressure-test whether we should invest in
MultiDriverBehaviorTest before publishing the article.
```

Annotated transcripts live under `pressure-test-testing-decisions/examples/`.

## Layout

```text
skills/
  pressure-test-decisions/           # vendored upstream
    SKILL.md
    NOTICE
    agents/openai.yaml
    references/{methods,books}.md
  pressure-test-testing-decisions/   # article companion
    SKILL.md
    agents/openai.yaml
    references/{methods,examples}.md
    examples/*.md
```

Conventions match the upstream package: kebab-case directory === frontmatter `name`; required `description`; progressive disclosure via `references/`; optional `agents/openai.yaml`.

## Adding a skill

1. Create `skills/<skill-name>/SKILL.md`.
2. Add valid `name` and `description` YAML frontmatter.
3. Keep references and examples inside the same skill directory.
4. Run `npx skills add . --list` before committing.

## How this supports the article

| Article theme | Skill move |
| --- | --- |
| Weak tests as false insurance | Premortem: green CI, wrong product |
| Behavior vs implementation | Oracle audit vs call-order mocks |
| Stub vs mock | Stub-at-port rule |
| Runtime coupling | Driver-swap test |
| What to ask of an agent | Invoke `$pressure-test-testing-decisions` before generating tests |

The skills challenge judgment. They do not invent PHPUnit cases unless you explicitly ask after the decision record is locked.
