# NoLife Agency Client Website Demo

Run the local demo:

`php bin/console nolife:demo:client-website acme-saas`

Output directory:

`var/nolife-agency/runs/{run_id}/`

Expected files:

- `brief.json`
- `landing-page.md`
- `seo-geo.md`
- `article-draft.md`
- `media-brief.yaml`
- `delivery-report.md`
- `trace.json`
- `audit.jsonl`

Required workflow states:

- `lead_qualified`
- `brief_created`
- `architecture_defined`
- `content_generated`
- `seo_optimized`
- `media_brief_created`
- `delivery_reviewed`
- `human_approval_required`

Paperclip is modeled as an organizational concept. Hermes is modeled as an agent specialization concept.
