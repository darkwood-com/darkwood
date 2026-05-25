| Q             | A
| ------------- | ---
| Branch?       | `8.0`
| Bug fix?      | yes/no
| New feature?  | yes/no <!-- if yes, update package CHANGELOG.md and CHANGELOG-8.0.md -->
| BC break?     | yes/no <!-- if yes, update CHANGELOG-8.0.md, package CHANGELOG.md, and UPGRADE-8.0.md -->
| Tests added?  | yes/no
| Issues        | Fix #... <!-- prefix each issue with "Fix #" -->
| License       | MIT

<!--
Replace this comment with a concise description of the change:
- What it does and why it is needed
- How to verify (commands run locally)

Checklist:
- [ ] Changes are scoped to the correct package directory (or monorepo docs/CI)
- [ ] Tests pass in the affected package (`make phpunit`, as in Navi)
- [ ] PHP CS Fixer / PHPStan pass when applicable
- [ ] Package CHANGELOG.md updated (Darkwood version section, not independent semver)
- [ ] CHANGELOG-8.0.md updated for user-visible changes
- [ ] UPGRADE-8.0.md updated if BC break
- [ ] No `vendor/`, `var/`, caches, or secrets committed
-->
