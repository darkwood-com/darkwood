# Public history rewrite on 8 November 2026

The `1.x` branch history was rewritten on 8 November 2026 to keep deployment-specific documentation out of the Open Source application repository. The application changes were preserved; only operational material that did not belong in this repository was removed or generalized.

The previous public tip `4bcc91b286b7ae5b849e008c606f75688cba7f48` is not an ancestor of the rewritten branch. Contributors with a clone or branch based on the former history should not merge the two lineages. Fetch the new branch and either rebase the commits that are still relevant onto `origin/1.x`, or create a fresh clone and cherry-pick those commits.

Before replacing a local branch, preserve uncommitted work and record any local commit that is not available elsewhere. A safe recovery procedure is:

```console
git fetch origin
git switch -c recovery/pre-history-rewrite
git switch --create 1.x-rewritten origin/1.x
```

Review and cherry-pick only application changes from the recovery branch. Do not copy deployment credentials, environment files or private infrastructure documentation into this repository.

Git history rewriting cannot erase copies already present in forks, clones or third-party caches. No credential should ever be considered protected by a history rewrite; any exposed credential must be revoked independently.
