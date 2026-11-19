# DarkwoodGame on the existing MCP server

Verified against local implementation and production OpenAPI on 19 November 2026. The four tools were already declared in API Platform metadata; this iteration qualifies and secures their actual execution. There is no second MCP server. The existing Symfony MCP Bundle uses the official PHP SDK, locked here to 0.9.0. The separate hackathon candidate retains SDK 0.10.0.

## Operations and real contracts

The existing HTTP endpoint is `/mcp` on the configured API host. Tools preserve their existing names:

- `get_darkwood_state` → `GET /api/darkwood/state`, no arguments. Returns the actual JSON map, including `user`, `state`, `mode`, `display`; additional fields depend on the screen. Without a player JWT it can return `user: null`, `state: not-logged`. Do not invent resources absent from the response.
- `list_darkwood_archives` → `GET /api/darkwood/archives`, no arguments. Returns `archives`, an array of `{id, date}` entries. Requires an active beta **and premium** API key. These are shared historical snapshots, not each player's personal saves.
- `get_darkwood_archive` → `GET /api/darkwood/archives/{id}`, input `id` is an actual calendar date `YYYY-MM-DD`. Same premium gate; unknown dates return a caller-facing error. Returns the stored snapshot, not a reconstructed current state.
- `darkwood_action` → `POST /api/darkwood/action`, input `query` object. Potentially changes the current player's persistent data or session. Requires authenticated player, active beta key, existing daily quota and exact action approval by the trusted client.

Production OpenAPI advertises a generic DarkwoodGame object and a 201 response for the POST. The implementation returns dynamic JSON maps, normally HTTP 200. The runtime contract and examples must be checked alongside OpenAPI. No parameters were inferred from an OGame-like model or another game.

The supported MCP action subset deliberately uses existing API names: `state` in `main`, `combat`, `daily-battle`, `info`; optional `mode: combat`; at most one of `actionBeginFight`, `actionFight`, `actionEndFight`, `actionUsePotion`, `actionAddPoint`. Fight flags accept only `true`, `1` or `"1"`; potion is not valid in daily battles. Actions during a fight require `mode: combat`. `actionAddPoint` is limited to `strength`, `dexterity`, `vitality` in `info`. Other shop/equipment operations remain accessible through the ordinary API; they are not silently enabled through this qualified MCP subset. Unknown fields, compound actions and invalid dates are rejected before forwarding.

## Authentication and observation

Send `X-API-Key` and, for player-specific state/action, `Authorization: Bearer <player JWT>`. They are transport headers, never tool arguments. The `/mcp` firewall validates the same JWT as the API in a separate context; it does not inherit a partially authenticated website/2FA context. Hello CV remains publicly accessible under its existing metadata; the unrelated Identity firewalls and callbacks are unchanged.

Forwarding retains the configured API host, authorization headers and active session. Marked MCP subrequests pass through the **existing** beta access and daily action limit subscribers, rather than bypassing their main-request guards. Successful results are JSON, not JSON-LD collections that discard associative field names. HTTP error statuses become public-safe MCP errors; arbitrary debug bodies, HTML redirects and credential fields are not relayed.

The legacy state GET can create a missing player. The MCP observation refuses that initialization with an explanatory 409: initialize through the normal game interface first. No arbitrary state/action query parameters are forwarded by the observation tool. The archives GETs read shared premium snapshots. Read-only annotations document these qualified behaviors; annotations alone do not enforce security.

DNS rebinding protection remains active. The existing HTTP transport permits only the configured API host and loopback hosts; it is not disabled for public deployment. Cross-origin clients still need the application's configured origin policy.

## Explicit action approval

The model cannot approve its own action by supplying an extra JSON field. A trusted interactive client must show the intended player and exact query to the human, then provide a transport header for that single HTTP request:

1. Sort the query keys with PHP `ksort`.
2. Encode `['user_id' => <authenticated integer player account id>, 'query' => <sorted query>]` with PHP `json_encode(..., JSON_THROW_ON_ERROR)` and its default escaping.
3. Set `X-Darkwood-Action-Approval` to the SHA-256 hex digest, only after the human approves.
4. Send one MCP call. The server consumes the approval in the current request; a second mutation in the same request is refused. Do not retry a state-changing call automatically.

The digest binds consent to account and payload; it is **not** a secret, a signature, an expiring authorization token or an idempotency key. Human consent is the trusted client's responsibility. Never configure this header globally for an autonomous agent. A fully credentialed arbitrary HTTP client already has direct API access; this boundary does not claim to constrain such a client. The demonstration observer exposes only the state tool and never supplies this header.

## Local qualification and limits

The S47 isolated run boots this actual Portal kernel with a disposable SQLite database and generated local JWT keys. It initializes the official MCP server, lists its tools, authenticates a synthetic player, reads the actual main state, lists/reads a deliberately seeded local archive, refuses an unapproved action and accepts an explicitly approved `state: main` request. That last request is a non-combat menu request: it is **not evidence of an executed attack**. A request without the API key is refused.

Unit regressions exercise the relay, secret-field removal, HTTP errors, marked-subrequest beta/quota checks, consumed approval, malformed actions and dates. Fixture data are explicitly separated from the real API code and a real provider interpretation. No production player data are modified and no production credentials are required for this qualification. The pre-existing quota initialization used MySQL-only INSERT IGNORE. It now keeps that branch for MySQL/MariaDB and uses ON CONFLICT DO NOTHING on PostgreSQL/SQLite, with the existing guarded atomic increment unchanged. A real in-memory SQLite regression verifies two accepted increments, a refused third increment and a single persisted row; the zero-quota subscriber path is also covered. These tests do not claim to benchmark production concurrency.

The existing `content-mario-orquestration` card supplies a secondary `app:mario:observe-darkwood` command: a real Symfony AI agent uses the official SDK HTTP client and **only** the qualified state tool, then explains the actual response. The original level-design submission remains independent and executable without a Portal instance.

## Production verification — 19 November 2026

The scoped runtime update retained the production vendor set: API Platform 4.4.6,
MCP SDK 0.8.1, MCP Bundle 0.14.1, FrameworkBundle 8.1.10. Local qualification
used SDK 0.9.0 and MCP Bundle 0.15.0; the optional Mario HTTP client uses SDK 0.10.0.
Relevant loader/handler APIs were checked before deployment. No Composer update
or database migration was performed in production. Protocol initialization,
four-tool discovery and refusal of state access without an API key passed.
Authenticated player reads and game actions were not executed in production.

A disposable local database independently exercised all four operations, an
unapproved action refusal, an approved main-menu request and the free-key quota:
two accepted requests, the third refused, persisted usage count two. Premium
archive access was refused after downgrading the synthetic key. These are real
local API responses from synthetic data, not observations of a production player.
