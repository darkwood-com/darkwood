# Demo Run Guide

Run:

`php bin/console nolife:demo:client-website acme-saas`

Options:

- `--real-ai`
- `--real-flow`
- `--real-navi`
- `--real-media`
- `--dry-run`

Required outputs under `var/nolife-agency/runs/{run_id}/`:

- `brief.json`
- `audit.jsonl`
- `trace.json`
- `workflow-result.json`
- `delivery-report.md`
- `article-draft.md`
- `seo-geo.md`
- `media-brief.yaml`

Expected final state: `human_approval_required`.
