# Darkwood public catalogue

Canonical URL: <https://pages.darkwood.com/>. Hosting remains GitHub Pages for
`darkwood-com/darkwood`, using GitHub Actions (not a `gh-pages` branch).

`publish-monorepo-pages.yml` builds `.github/scripts/build-catalog-pages.mjs`
with Node 22 and uploads `dist/github-pages` using `upload-pages-artifact`.
`deploy-pages` publishes that artifact to the `github-pages` environment on
pushes to `main` or a manual workflow dispatch.

`.github/pages/CNAME` is the versioned domain source. The builder derives its
default HTTPS base URL from it and copies it into the artifact. Canonicals,
Open Graph, navigation, card JSON, sitemap and robots all use this base.
GitHub Actions Pages stores the custom domain in repository settings; GitHub
does not require or use the artifact CNAME for this publishing mode. Keep both
settings and the versioned domain aligned. Every build validates all catalogue
cards, their internal targets and metadata before upload.

DNS: Cloudflare, `pages.darkwood.com` CNAME `darkwood-com.github.io`, **DNS only**,
TTL automatic. No VPS, proxy or other service record is involved. Configure
the domain in repository Pages settings before creating its DNS record, then
verify DNS and the GitHub-issued certificate and enable Enforce HTTPS.

The former <https://darkwood-com.github.io/darkwood/> URL is retained by GitHub
Pages and redirects to the custom domain. Validate both the homepage and card
paths after changes. Do not add `/darkwood/` to custom-domain paths.

Local build and validation (Node 22 in the existing Nix environment):

```sh
node .github/scripts/build-catalog-pages.mjs
node .github/scripts/validate-catalog-pages.mjs
```

Rollback: revert the domain-related source commit without rewriting history,
restore the previous Pages custom-domain setting, and redeploy through the same
workflow. Remove only the `pages` DNS record if abandoning this custom domain.
Review domain ownership and DNS/HTTPS state before changing the live settings;
do not modify other Darkwood service records.
