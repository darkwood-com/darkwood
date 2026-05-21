| Q             | A
| ------------- | ---
| Branch?       | `1.x`
| Package?      | Navi / Flow / MediaBundle / IaExceptionBundle / monorepo
| Bug fix?      | yes/no
| New feature?  | yes/no <!-- if yes, update package CHANGELOG.md and CHANGELOG-1.x.md -->
| BC break?     | yes/no <!-- if yes, update CHANGELOG-1.x.md, package CHANGELOG.md, and UPGRADE-1.x.md -->
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
- [ ] CHANGELOG-1.x.md updated for user-visible changes
- [ ] UPGRADE-1.x.md updated if BC break
- [ ] No `vendor/`, `var/`, caches, or secrets committed
-->
