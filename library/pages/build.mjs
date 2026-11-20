import { createHash } from "node:crypto";
import { copyFile, mkdir, readFile, writeFile } from "node:fs/promises";
import { resolve } from "node:path";
import { validatePublicationDomains } from "../../.github/scripts/validate-publication-domains.mjs";

const root = resolve(import.meta.dirname, "../..");
const output = resolve(root, process.argv[2] ?? "library/pages/dist");
const catalog = JSON.parse(await readFile(resolve(root, "tags.json"), "utf8"));
const distribution = JSON.parse(await readFile(resolve(root, ".github/distribution-cards.json"), "utf8"));
validatePublicationDomains(distribution);

const customDomain = (await readFile(resolve(root, "library/pages/CNAME"), "utf8")).trim();
const siteUrl = new URL(process.argv[3] ?? `https://${customDomain}/`).href;
const repositoryUrl = "https://github.com/darkwood-com/darkwood";
const sourceBaseUrl = `${repositoryUrl}/tree/main/`;
const sourceAssets = resolve(root, "library/pages/assets");
const sourceFonts = resolve(root, "library/pages/assets/fonts");
const sepiaIcon = (await readFile(resolve(root, "library/darkwood-design/icons/sepia.svg"), "utf8")).trim();
const assetRevision = createHash("sha256")
  .update(await readFile(resolve(sourceAssets, "catalog.css")))
  .update(await readFile(resolve(sourceAssets, "catalog.js")))
  .update(sepiaIcon)
  .digest("hex").slice(0, 12);
const cardRedirects = new Map([
  ["adventofcode", "content-adventofcode"],
  ["php-pipe-operator", "content-php-pipe-operator"],
  ["flowvox-ios", "flowvox"],
]);

const escape = (value) => String(value ?? "")
  .replaceAll("&", "&amp;")
  .replaceAll("<", "&lt;")
  .replaceAll(">", "&gt;")
  .replaceAll('"', "&quot;")
  .replaceAll("'", "&#39;");
const titleCase = (value) => String(value).split("-").map((part) => ({
  ai: "AI", api: "API", github: "GitHub", ios: "iOS", mcp: "MCP", php: "PHP", rxjs: "RxJS", url: "URL",
}[part] ?? `${part.charAt(0).toUpperCase()}${part.slice(1)}`)).join(" ");
const plural = (count, word) => `${count} ${word}${count === 1 ? "" : "s"}`;

if (catalog.schemaVersion !== 3 || !Array.isArray(catalog.projects)) {
  throw new Error("tags.json must be a schema v3 catalog with a projects array");
}

const ids = new Set();
for (const project of catalog.projects) {
  if (!project.id || !project.path || !project.description || !project.kind || !project.status) {
    throw new Error(`Incomplete public card: ${project.id ?? "<unknown>"}`);
  }
  if (ids.has(project.id)) throw new Error(`Duplicate public card id: ${project.id}`);
  if (!project.path.startsWith("library/")) throw new Error(`Card path is outside library/: ${project.id}`);
  ids.add(project.id);
}

const projects = [...catalog.projects].sort((left, right) => left.id.localeCompare(right.id));
const projectById = new Map(projects.map((project) => [project.id, project]));
const publicationsFor = (project) => distribution.cards.filter((card) =>
  card.source === project.path || card.source?.startsWith(`${project.path}/`));
const publicLinksFor = (project) => {
  const links = [];
  for (const publication of publicationsFor(project)) {
    if (publication.repository && publication.repository !== "darkwood-com/darkwood") {
      links.push({ label: "Distribution repository", url: `https://github.com/${publication.repository}` });
    }
    if (publication.homepage?.domain) links.push({ label: "Public website", url: `https://${publication.homepage.domain}/` });
    if (publication.pages?.domain) links.push({ label: "Public website", url: `https://${publication.pages.domain}/` });
  }
  return links.filter((link, index, all) => all.findIndex((candidate) => candidate.url === link.url) === index);
};

const counts = projects.reduce((result, project) => {
  result.kinds[project.kind] = (result.kinds[project.kind] ?? 0) + 1;
  result.statuses[project.status] = (result.statuses[project.status] ?? 0) + 1;
  return result;
}, { kinds: {}, statuses: {} });

const icon = (name) => ({
  arrow: '<svg aria-hidden="true" viewBox="0 0 20 20"><path d="M4 10h11m-4-4 4 4-4 4"/></svg>',
  code: '<svg aria-hidden="true" viewBox="0 0 20 20"><path d="m7 5-5 5 5 5m6-10 5 5-5 5"/></svg>',
  external: '<svg aria-hidden="true" viewBox="0 0 20 20"><path d="M11 3h6v6m0-6-8 8M8 5H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-4"/></svg>',
}[name]);

const cardMarkup = (project, { compact = false } = {}) => {
  const detailUrl = `${siteUrl}cards/${encodeURIComponent(project.id)}/`;
  const topics = (project.topics ?? []).slice(0, compact ? 2 : 3);
  return `<article class="card${compact ? " card--compact" : ""}" data-card data-kind="${escape(project.kind)}" data-status="${escape(project.status)}" data-name="${escape(project.id)}" data-search="${escape([project.id, project.description, ...(project.topics ?? []), ...(project.dependencies ?? [])].join(" ").toLowerCase())}">
    <div class="card__meta"><span class="eyebrow">${escape(project.kind)}</span><span class="status status--${escape(project.status)}"><i></i>${escape(project.status)}</span></div>
    <h3><a href="${detailUrl}">${escape(titleCase(project.id))}</a></h3>
    <p>${escape(project.description)}</p>
    ${topics.length ? `<ul class="topic-list" aria-label="Topics">${topics.map((topic) => `<li>${escape(topic)}</li>`).join("")}</ul>` : ""}
    <a class="card__link" href="${detailUrl}" aria-label="Explore ${escape(titleCase(project.id))}">Explore card ${icon("arrow")}</a>
  </article>`;
};

const pageShell = ({ title, description, canonical, body, pageClass = "" }) => `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="color-scheme" content="dark">
  <meta name="theme-color" content="#080a0f">
  <meta name="description" content="${escape(description)}">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Darkwood">
  <meta property="og:title" content="${escape(title)}">
  <meta property="og:description" content="${escape(description)}">
  <meta property="og:url" content="${escape(canonical)}">
  <meta name="twitter:card" content="summary">
  <link rel="canonical" href="${escape(canonical)}">
  <link rel="icon" href="${siteUrl}assets/mark.svg" type="image/svg+xml">
  <script>try{document.documentElement.dataset.theme=localStorage.getItem("darkwood-theme")||(matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light")}catch(e){document.documentElement.dataset.theme="light"}</script>
  <link rel="stylesheet" href="${siteUrl}assets/catalog.css?v=${assetRevision}">
  <script type="module" src="${siteUrl}assets/catalog.js?v=${assetRevision}"></script>
  <title>${escape(title)}</title>
</head>
<body class="${escape(pageClass)}">
  <template id="sepia-theme-icon">${sepiaIcon}</template>
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header">
    <a class="brand" href="${siteUrl}" aria-label="Darkwood home"><span class="brand__mark">D</span><span>Darkwood</span></a>
    <nav aria-label="Primary navigation">
      <a href="${siteUrl}#projects">Projects</a>
      <a href="${siteUrl}#catalogue">Catalogue</a>
      <a href="${repositoryUrl}">GitHub ${icon("external")}</a>
      <button class="theme-switcher" type="button" data-theme-switcher aria-label="Change color theme"><span data-theme-icon aria-hidden="true">☼</span></button>
    </nav>
  </header>
  ${body}
  <nav class="mobile-nav" aria-label="Mobile navigation">
    <a href="${siteUrl}" data-mobile-home><span aria-hidden="true">⌂</span>Home</a>
    <a href="${siteUrl}#catalogue" data-mobile-catalogue><span aria-hidden="true">▦</span>Catalogue</a>
    <a href="${repositoryUrl}">${icon("external")}GitHub</a>
  </nav>
  <footer class="site-footer">
    <div><span class="brand__mark">D</span><p>Built from the public Darkwood catalogue.<br><span>Schema v${escape(catalog.schemaVersion)} · ${plural(projects.length, "card")}</span></p></div>
    <div class="footer__links"><a href="${repositoryUrl}">Source</a><a href="${repositoryUrl}/blob/main/CONTRIBUTING.md">Contribute</a><a href="${repositoryUrl}/blob/main/tags.json">Catalogue JSON</a></div>
  </footer>
</body>
</html>`;

const kindOptions = Object.keys(counts.kinds).sort().map((kind) => `<option value="${escape(kind)}">${escape(titleCase(kind))} (${counts.kinds[kind]})</option>`).join("");
const statusOptions = ["working", "backlog", "done"].filter((status) => counts.statuses[status]).map((status) => `<option value="${escape(status)}">${escape(titleCase(status))} (${counts.statuses[status]})</option>`).join("");
const kindGroups = Object.keys(counts.kinds).sort().map((kind) => {
  const cards = projects.filter((project) => project.kind === kind).map((project) => cardMarkup(project)).join("\n");
  return `<section class="catalogue-group" data-group="${escape(kind)}" aria-labelledby="group-${escape(kind)}">
    <div class="section-heading section-heading--group"><div><span class="eyebrow">Category</span><h3 id="group-${escape(kind)}">${escape(titleCase(kind))}</h3></div><span class="group-count" data-group-count>${counts.kinds[kind]}</span></div>
    <div class="card-grid">${cards}</div>
  </section>`;
}).join("\n");
const working = projects.filter((project) => project.status === "working").slice(0, 6);

const home = pageShell({
  title: "Darkwood — Public projects, components and creative work",
  description: `Explore ${projects.length} public Darkwood projects, open-source components, experiments and published work.`,
  canonical: siteUrl,
  pageClass: "home",
  body: `<main id="main">
    <section class="hero">
      <div class="hero__copy"><span class="eyebrow">Public monorepo · Open ecosystem</span><h1>Ideas become<br><em>working systems.</em></h1><p>Darkwood is a living collection of open-source components, products, experiments and creative work — developed together in one public monorepo.</p><div class="hero__actions"><a class="button button--primary" href="#catalogue">Explore the catalogue ${icon("arrow")}</a><a class="button" href="${repositoryUrl}">${icon("code")} View source</a></div></div>
      <aside class="hero__panel" aria-label="Catalogue overview"><div class="signal"><span>Catalogue status</span><strong><i></i> Live</strong></div><div class="metric"><strong>${projects.length}</strong><span>public cards</span></div><div class="metric-row"><div><strong>${counts.statuses.working ?? 0}</strong><span>in progress</span></div><div><strong>${counts.kinds.content ?? 0}</strong><span>published works</span></div></div><div class="kind-bars">${Object.entries(counts.kinds).sort(([, a], [, b]) => b - a).map(([kind, count]) => `<div><span>${escape(kind)}</span><i style="--size:${Math.max(8, Math.round(count / projects.length * 100))}%"></i><b>${count}</b></div>`).join("")}</div></aside>
    </section>
    <section class="manifesto" id="projects"><div><span class="eyebrow">One source, many outcomes</span><h2>A monorepo built for discovery.</h2></div><div class="manifesto__copy"><p>Every public card has a stable identity, a clear purpose and a place in the ecosystem. Products stay active, components remain reusable, and completed work keeps its provenance.</p><a href="${repositoryUrl}/blob/main/README.md">How the monorepo works ${icon("arrow")}</a></div></section>
    <section class="featured" aria-labelledby="featured-title"><div class="section-heading"><div><span class="eyebrow">Currently moving</span><h2 id="featured-title">Projects in progress</h2></div><a href="?status=working#catalogue">View all ${counts.statuses.working ?? 0} working cards ${icon("arrow")}</a></div><div class="card-grid card-grid--featured">${working.map((project) => cardMarkup(project, { compact: true })).join("\n")}</div></section>
    <section class="catalogue" id="catalogue" aria-labelledby="catalogue-title">
      <div class="section-heading"><div><span class="eyebrow">Browse everything</span><h2 id="catalogue-title">Public catalogue</h2><p>Search descriptions, filter the collection and open any card for its canonical details.</p></div><span class="catalogue-total">${projects.length}<small>cards</small></span></div>
      <form class="filters" data-filters role="search"><label class="search-field"><span>Search cards</span><div><svg aria-hidden="true" viewBox="0 0 20 20"><circle cx="9" cy="9" r="6"/><path d="m13.5 13.5 4 4"/></svg><input type="search" name="query" placeholder="Name, description, topic…" autocomplete="off"></div></label><label><span>Kind</span><select name="kind"><option value="">All kinds</option>${kindOptions}</select></label><label><span>Status</span><select name="status"><option value="">All statuses</option>${statusOptions}</select></label><label><span>Sort</span><select name="sort"><option value="name-asc">A → Z</option><option value="name-desc">Z → A</option></select></label><button class="button button--reset" type="reset">Reset</button></form>
      <div class="results-bar"><p aria-live="polite"><strong data-result-count>${projects.length}</strong> results</p><p>Grouped by category</p></div><div data-catalogue-groups>${kindGroups}</div><div class="empty-state" data-empty hidden><span>∅</span><h3>No cards found</h3><p>Try another term or reset the filters.</p></div>
    </section>
  </main>`,
});

const detailPages = projects.map((project) => {
  const title = titleCase(project.id);
  const sourceUrl = `${sourceBaseUrl}${project.path}`;
  const links = publicLinksFor(project);
  const dependencies = (project.dependencies ?? []).map((dependency) => projectById.has(dependency) ? `<a href="${siteUrl}cards/${encodeURIComponent(dependency)}/">${escape(dependency)}</a>` : `<span>${escape(dependency)}</span>`).join("");
  const related = projects.filter((candidate) => candidate.id !== project.id && (candidate.topics ?? []).some((topic) => (project.topics ?? []).includes(topic))).slice(0, 3);
  return { project, html: pageShell({
    title: `${title} — Darkwood`, description: project.description, canonical: `${siteUrl}cards/${encodeURIComponent(project.id)}/`, pageClass: "detail",
    body: `<main id="main"><section class="detail-hero"><a class="back-link" href="${siteUrl}#catalogue">${icon("arrow")} Back to catalogue</a><div class="detail-hero__grid"><div><div class="card__meta"><span class="eyebrow">${escape(project.kind)}</span><span class="status status--${escape(project.status)}"><i></i>${escape(project.status)}</span></div><h1>${escape(title)}</h1><p>${escape(project.description)}</p><div class="hero__actions"><a class="button button--primary" href="${sourceUrl}">${icon("code")} Browse source</a>${links.map((link) => `<a class="button" href="${escape(link.url)}">${escape(link.label)} ${icon("external")}</a>`).join("")}</div></div><aside class="identity-panel"><span>Canonical identity</span><strong>${escape(project.id)}</strong><dl><div><dt>Path</dt><dd>${escape(project.path)}</dd></div><div><dt>Domain</dt><dd>${escape(project.domain)}</dd></div><div><dt>Lifecycle</dt><dd>${escape(project.status)}</dd></div></dl></aside></div></section><section class="detail-content"><div><span class="eyebrow">Metadata</span><h2>About this card</h2><p>The public catalogue describes this card as a <strong>${escape(project.kind)}</strong> in the <strong>${escape(project.domain)}</strong> domain. Its lifecycle status is <strong>${escape(project.status)}</strong>.</p></div><div class="metadata-grid"><section><h3>Topics</h3>${project.topics?.length ? `<ul class="topic-list">${project.topics.map((topic) => `<li>${escape(topic)}</li>`).join("")}</ul>` : "<p>No topics are recorded.</p>"}</section><section><h3>Public dependencies</h3>${dependencies ? `<div class="dependency-list">${dependencies}</div>` : "<p>No public dependencies are recorded.</p>"}</section><section><h3>Canonical source</h3><p><a href="${sourceUrl}">${escape(project.path)} ${icon("external")}</a></p></section></div></section>${related.length ? `<section class="related"><div class="section-heading"><div><span class="eyebrow">Keep exploring</span><h2>Related cards</h2></div></div><div class="card-grid">${related.map((candidate) => cardMarkup(candidate, { compact: true })).join("")}</div></section>` : ""}</main>`,
  }) };
});

const cardsJson = projects.map((project) => ({ ...project, title: titleCase(project.id), source: `${sourceBaseUrl}${project.path}`, publicLinks: publicLinksFor(project), detail: `${siteUrl}cards/${encodeURIComponent(project.id)}/` }));
const sitemap = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n  <url><loc>${siteUrl}</loc></url>\n${projects.map((project) => `  <url><loc>${siteUrl}cards/${encodeURIComponent(project.id)}/</loc></url>`).join("\n")}\n</urlset>\n`;
const notFound = pageShell({ title: "Page not found — Darkwood", description: "The requested Darkwood page could not be found.", canonical: `${siteUrl}404.html`, pageClass: "not-found", body: `<main id="main"><section class="error-page"><span>404</span><h1>Lost in the woods.</h1><p>This path does not match a public Darkwood card.</p><a class="button button--primary" href="${siteUrl}">Return to the catalogue ${icon("arrow")}</a></section></main>` });

await mkdir(resolve(output, "assets"), { recursive: true });
await mkdir(resolve(output, "assets/fonts"), { recursive: true });
await mkdir(resolve(output, "data"), { recursive: true });
await writeFile(resolve(output, "index.html"), home);
await writeFile(resolve(output, "404.html"), notFound);
await writeFile(resolve(output, "data/cards.json"), `${JSON.stringify(cardsJson)}\n`);
await writeFile(resolve(output, "sitemap.xml"), sitemap);
await writeFile(resolve(output, "robots.txt"), `User-agent: *\nAllow: ${new URL(siteUrl).pathname}\nSitemap: ${siteUrl}sitemap.xml\n`);
await copyFile(resolve(root, "library/pages/CNAME"), resolve(output, "CNAME"));
await writeFile(resolve(output, ".nojekyll"), "");
await copyFile(resolve(sourceAssets, "catalog.css"), resolve(output, "assets/catalog.css"));
await copyFile(resolve(sourceAssets, "catalog.js"), resolve(output, "assets/catalog.js"));
await copyFile(resolve(sourceAssets, "mark.svg"), resolve(output, "assets/mark.svg"));
for (const font of ["Jost-400-Book.otf", "Jost-500-Medium.otf", "Jost-600-Semi.otf", "Jost-700-Bold.otf", "OFL.txt"]) {
  await copyFile(resolve(sourceFonts, font), resolve(output, "assets/fonts", font));
}
for (const { project, html } of detailPages) {
  const directory = resolve(output, "cards", project.id);
  await mkdir(directory, { recursive: true });
  await writeFile(resolve(directory, "index.html"), html);
}
for (const [legacyId, canonicalId] of cardRedirects) {
  if (!projectById.has(canonicalId)) throw new Error(`Unknown redirect target: ${canonicalId}`);
  const target = `${siteUrl}cards/${encodeURIComponent(canonicalId)}/`;
  const directory = resolve(output, "cards", legacyId);
  await mkdir(directory, { recursive: true });
  await writeFile(resolve(directory, "index.html"), `<!doctype html>\n<html lang="en">\n<head>\n  <meta charset="utf-8">\n  <meta name="robots" content="noindex">\n  <link rel="canonical" href="${escape(target)}">\n  <meta http-equiv="refresh" content="0; url=${escape(target)}">\n  <title>Card moved — Darkwood</title>\n</head>\n<body><p>This card moved to <a href="${escape(target)}">${escape(canonicalId)}</a>.</p></body>\n</html>\n`);
}
console.log(`Generated ${projects.length} public cards and ${detailPages.length} detail pages in ${output}`);
