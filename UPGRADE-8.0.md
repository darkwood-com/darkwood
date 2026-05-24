UPGRADE FROM pre-unified releases to 1.3.x
============================================

Darkwood **1.3.0** introduces unified global versioning. All packages now
share the same version line and are released together from the monorepo.

## Upgrading to unified v1.3.0

If you previously installed packages with independent versions, update your
`composer.json` constraints to the unified line:

```diff
  "require": {
-     "darkwood/flow": "^1.2",
-     "darkwood/navi": "^1.0",
-     "darkwood/media-bundle": "^1.0",
-     "darkwood/ia-exception-bundle": "^1.0",
+     "darkwood/flow": "^1.3",
+     "darkwood/navi": "^1.3",
+     "darkwood/media-bundle": "^1.3",
+     "darkwood/ia-exception-bundle": "^1.3",
  }
```

Then run:

```bash
composer update darkwood/*
```

### Prior independent versions (superseded)

These satellite tags remain on GitHub for history but are **not** part of the
unified release line:

| Package | Last pre-unified tag |
|---------|---------------------|
| `darkwood/flow` | `v1.2.6` |
| `darkwood/navi` | `v1.0.2` |
| `darkwood/media-bundle` | `v1.0.0` |
| `darkwood/ia-exception-bundle` | `v1.0.4` |

From **v1.3.0** onward, install `^1.3` (or a specific unified tag such as
`1.3.0`) for every Darkwood package you use.

## Breaking changes in 8.0

Breaking changes will be listed below with the `[BC BREAK]` prefix as they are
introduced in future releases.
