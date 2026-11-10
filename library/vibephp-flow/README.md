# VibePHP Flow — Profile Demo

**Request execution worker** using [darkwood/flow](https://flow.darkwood.com) to structure the VibePHP pipeline as observable Jobs instead of an opaque controller.

## Run the worker

```bash
bin/console app:flow:profile-demo --limit=1 -vv
bin/console app:flow:profile-demo -vv
```

## Pipeline steps

Each queued HTTP request flows through five Jobs (mirroring `VibeController`):

1. **ResolveScriptJob** — map path to `vibe/` script
2. **ReadSourceJob** — `file_get_contents` on the script
3. **BuildPromptJob** — assemble JSON context for the runtime
4. **FakeExecuteJob** — deterministic fake execution (regex, includes, hash loop)
5. **LogMetricsJob** — timing, memory, status table

No Laravel or AI dependency — reproducible profiles.

## Profiling with Blackfire

```bash
composer require --dev blackfire/php-sdk
```

`.env.local`:

```
BLACKFIRE_CLIENT_ID=…
BLACKFIRE_CLIENT_TOKEN=…
```

```bash
bin/console app:flow:profile-demo -vv
kill -USR2 <pid>   # start profiling
kill -USR2 <pid>   # stop → profile URL in logs
```

### What to look for in Blackfire

- **ReadSourceJob** I/O (`file_get_contents`)
- **BuildPromptJob** `json_encode` string assembly
- **FakeExecuteJob** regex + include discovery + hash loop
- Per-request iterations visible in the consumer loop
