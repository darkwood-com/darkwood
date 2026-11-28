# Darkwood public catalogue

Canonical URL: <https://page.darkwood.com/>. Hosting remains GitHub Pages for
`darkwood-com/darkwood`, using GitHub Actions (not a `gh-pages` branch).

`publish-monorepo-pages.yml` builds `library/page/build.mjs`
with Node 22 and uploads `library/page/dist` using `upload-pages-artifact`.
`deploy-pages` publishes that artifact to the `github-pages` environment on
pushes to `main` or a manual workflow dispatch.

`library/page/CNAME` is the versioned domain source. The builder derives its
default HTTPS base URL from it and copies it into the artifact. Canonicals,
Open Graph, navigation, card JSON, sitemap and robots all use this base.
GitHub Actions Pages stores the custom domain in repository settings; GitHub
does not require or use the artifact CNAME for this publishing mode. Keep both
settings and the versioned domain aligned. Every build validates all catalogue
cards, their internal targets and metadata before upload.

Cloudflare maps `page.darkwood.com` to `darkwood-com.github.io`.
GitHub Pages serves the catalogue over HTTPS. The former custom host
`pages.darkwood.com` is retired.

The former <https://darkwood-com.github.io/darkwood/> URL is retained by GitHub
Pages and redirects to the custom domain. Validate both the homepage and card
paths after changes. Do not add `/darkwood/` to custom-domain paths.

Local build and validation (Node 22 in the existing Nix environment):

```sh
node library/page/build.mjs
node library/page/validate.mjs
```

Rollback: revert the domain-related source commit without rewriting history,
restore the previous Pages custom-domain setting, and redeploy through the same
workflow. Remove only the `page` DNS record if abandoning this custom domain.
Review domain ownership and DNS/HTTPS state before changing the live settings;
do not modify other Darkwood service records.

## Official Darkwood hostname convention

Since 18 November 2026, new Darkwood websites use one label below `darkwood.com`:
`<name-function>-<category>.darkwood.com`. Flatten existing web labels in their
original order: `memory.content.darkwood.com` becomes
`memory-content.darkwood.com`. Keep `darkwood.com` and existing single-level
names unchanged. Check name collisions and the 63-byte DNS label limit before
changing DNS or Pages settings. DKIM, BIMI, SRV and other protocol-specific DNS
names are exceptions and must retain their required structure.

The distribution manifest is the source for the target website names. Validate
DNS, HTTPS and generated assets for each site before activating its new URL.
For the 18 November migration, the owner explicitly waived compatibility and
redirects for the three former `*.content.darkwood.com` sites. Remove their old
DNS entries only after the new destinations pass verification, to avoid
leaving orphaned Pages CNAMEs. Do not promise automatic browser-state transfer:
localStorage belongs to the former origin and is not deleted by this migration.

GitHub Pages accepts one custom domain per site. Keeping the former DNS record
alone does not provide a redirect from a former custom domain. Future API, MCP,
WebSocket, POST or OAuth migrations must examine the client and callback
contract; a 301 is not assumed transparent. Existing single-level service names,
Identity endpoints and protocol DNS records remain unchanged.

## Card ownership

This card owns the generator, public assets and `dist/`. Inputs are the root public
`tags.json` and `.github/distribution-cards.json`; private catalogues are never read.
The builder copies the compiled DarkwoodBundle styles, Jost fonts and their OFL notice. No runtime dependency on
Uniflow or another card distribution is required. The separate Symfony AI MCP
workshop owns its own distribution and keeps its existing artifact repository.
