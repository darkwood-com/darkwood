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

## Upgrading to 8.1.3

Update every `darkwood/*` package you use to `^8.1` (or pin `8.1.3`), then run:

```bash
composer update darkwood/* symfony/*
```

## Upgrading to 8.1.2

Update every `darkwood/*` package you use to `^8.1` (or pin `8.1.2`), then run:

```bash
composer update darkwood/* symfony/*
```

### Symfony AI (`darkwood/ia-exception-bundle`)

**8.1.2** requires Symfony AI **0.12**. If you depend on
`darkwood/ia-exception-bundle`, align your Symfony AI packages before updating:

```diff
  "require": {
-     "symfony/ai-bundle": "^0.10.0",
-     "symfony/ai-agent": "^0.10.0",
-     "symfony/ai-platform": "^0.10.0",
+     "symfony/ai-bundle": "^0.12.0",
+     "symfony/ai-agent": "^0.12.0",
+     "symfony/ai-platform": "^0.12.0",
      "darkwood/ia-exception-bundle": "^8.1",
  }
```

Then run:

```bash
composer update darkwood/ia-exception-bundle symfony/ai-bundle symfony/ai-agent symfony/ai-platform
```

If your application also uses other Symfony AI bridges or bundles (for example
`symfony/ai-open-ai-platform` or `symfony/mcp-bundle`), bump them to `^0.12.0`
in the same `composer update` so Composer resolves a single `symfony/ai-platform`
version.

## Upgrading to 8.1.1

Update every `darkwood/*` package you use to `^8.1` (or pin `8.1.1`), then run:

```bash
composer update darkwood/* symfony/*
```

### Symfony AI (`darkwood/ia-exception-bundle`)

**8.1.1** requires Symfony AI **0.10**. If you depend on
`darkwood/ia-exception-bundle`, align your Symfony AI packages before updating:

```diff
  "require": {
-     "symfony/ai-bundle": "^0.8.0",
-     "symfony/ai-agent": "^0.8.0",
-     "symfony/ai-platform": "^0.8.0",
+     "symfony/ai-bundle": "^0.10.0",
+     "symfony/ai-agent": "^0.10.0",
+     "symfony/ai-platform": "^0.10.0",
      "darkwood/ia-exception-bundle": "^8.1",
  }
```

Then run:

```bash
composer update darkwood/ia-exception-bundle symfony/ai-bundle symfony/ai-agent symfony/ai-platform
```

If your application also uses other Symfony AI bridges or bundles (for example
`symfony/ai-open-ai-platform` or `symfony/mcp-bundle`), bump them to `^0.10.0`
in the same `composer update` so Composer resolves a single `symfony/ai-platform`
version.

### Inter-package constraints (`darkwood/media-bundle`)

**8.1.1** raises the Flow dependency to the unified `8.1` line:

```diff
  "require": {
-     "darkwood/flow": "^8.0",
+     "darkwood/flow": "^8.1",
      "darkwood/media-bundle": "^8.1",
  }
```

## Breaking changes in 8.1

Breaking changes will be listed below with the `[BC BREAK]` prefix as they are
introduced in future releases.
