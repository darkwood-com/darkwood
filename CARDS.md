# Darkwood cards: status and physical loading

Every `tags.json` entry has independent metadata:

- `kind` says what the card is: `product`, `component`, `content`, `tool`, `infrastructure`, `operations`, `experiment`, or a justified `other`;
- `status` follows the Joplin convention: `backlog`, `working`, or `done`;
- `dependencies` lists stable card identifiers needed by the card;
- `loaded` / `unloaded` is computed from the current Git worktree and is never stored in the catalogue.

## Statuses

- `backlog`: considered for future work, but not currently being built.
- `working`: under development, preparation, publication, or active operational work.
- `done`: the intended work is complete. A production application can remain loaded and operated while its status is `done`.

Status does not delete source, remove a sparse path, clean artifacts, or unpublish a URL.

## Commands

Run through the repository's Nix environment with PHP, Git and `lsof` available:

```bash
bin/darkwood cards list
bin/darkwood cards status
bin/darkwood cards load darkwood-portal
bin/darkwood cards unload darkwood-portal
bin/darkwood cards profile list
bin/darkwood cards profile show vps4-production
bin/darkwood cards profile apply vps4-production
```

Full checkouts report every card as loaded. `unload` is intentionally refused until sparse checkout is enabled. Dependencies are displayed but never auto-loaded: package dependencies can be satisfied by installed vendors and each environment profile remains the authority for source materialization.

Before an unload, the command refuses to continue when it finds tracked or untracked changes, ignored files, a loaded dependent card, open processes, Caddy/systemd references, an external symlink, or a card protected by the active environment profile. Persistent data remains outside the Git operation. The command never invokes `git clean`, deletes Git history, or removes `/var/lib` data.

## Environment profiles

`card-profiles.json` contains simple worktree selections for M4, M1, GrokBot, VPS-4 box and VPS-4 production. The M4 profile stays full. The box profile is documentation-only until its historical `8.1` branch policy is resolved. Profiles describe source materialization only; they do not manage services or persistent data.

## Content classification

A content card primarily produces, presents, or preserves editorial or communication material. An application that merely processes content remains a tool or product. The stable `id` is retained when a folder gains a `content-` prefix.
