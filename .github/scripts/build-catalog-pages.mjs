import { mkdir, readFile, writeFile } from "node:fs/promises";
import { resolve } from "node:path";

const root = resolve(import.meta.dirname, "../..");
const output = resolve(root, process.argv[2] ?? "dist/github-pages");
const catalog = JSON.parse(await readFile(resolve(root, "tags.json"), "utf8"));
const distribution = JSON.parse(await readFile(resolve(root, ".github/distribution-cards.json"), "utf8"));

const escape = (value) => String(value ?? "")
  .replaceAll("&", "&amp;")
  .replaceAll("<", "&lt;")
  .replaceAll(">", "&gt;")
  .replaceAll('"', "&quot;")
  .replaceAll("'", "&#39;");

const repositories = new Map(distribution.cards.map((card) => [card.id, card.repository]));
const projects = [...catalog.projects].sort((left, right) => left.id.localeCompare(right.id));
const counts = projects.reduce((result, project) => {
  result[project.kind] = (result[project.kind] ?? 0) + 1;
  return result;
}, {});

const cards = projects.map((project) => {
  const repository = repositories.get(project.id);
  const link = repository
    ? `https://github.com/${repository}`
    : `https://github.com/darkwood-com/darkwood/tree/main/${project.path}`;
  return `<article class="card" data-kind="${escape(project.kind)}" data-status="${escape(project.status)}">
    <div class="labels"><span>${escape(project.kind)}</span><span>${escape(project.status)}</span></div>
    <h2><a href="${escape(link)}">${escape(project.id)}</a></h2>
    <p>${escape(project.description)}</p>
    <small>${escape(project.path)}</small>
  </article>`;
}).join("\n");

const kindSummary = Object.entries(counts)
  .sort(([left], [right]) => left.localeCompare(right))
  .map(([kind, count]) => `<li><strong>${count}</strong> ${escape(kind)}</li>`)
  .join("");

const html = `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Darkwood public projects, components and content.">
  <title>Darkwood</title>
  <style>
    :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #090b10; color: #f6f7fb; }
    body { margin: 0; }
    header, main, footer { max-width: 1180px; margin: auto; padding: 2rem; }
    header { padding-top: 5rem; }
    h1 { font-size: clamp(3rem, 9vw, 7rem); line-height: .9; margin: 0; color: #3579f6; }
    header p { max-width: 720px; font-size: 1.2rem; color: #b8c0d4; }
    ul { display: flex; flex-wrap: wrap; gap: .75rem; padding: 0; list-style: none; }
    li, .labels span { border: 1px solid #293047; border-radius: 999px; padding: .4rem .75rem; color: #b8c0d4; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 1rem; }
    .card { border: 1px solid #20263a; border-radius: 14px; padding: 1.1rem; background: #10131d; }
    .card h2 { margin: 1rem 0 .5rem; font-size: 1.15rem; }
    .card p { min-height: 3.4rem; color: #b8c0d4; }
    .card small { color: #77819a; overflow-wrap: anywhere; }
    .labels { display: flex; gap: .4rem; }
    .labels span { font-size: .72rem; padding: .2rem .5rem; }
    a { color: #77a6ff; text-decoration: none; }
    a:hover { text-decoration: underline; }
    footer { color: #77819a; }
  </style>
</head>
<body>
  <header>
    <h1>Darkwood</h1>
    <p>A public ecosystem of ${projects.length} projects, components, experiments and published content. Development happens in the canonical monorepo.</p>
    <ul>${kindSummary}</ul>
  </header>
  <main><section class="grid">${cards}</section></main>
  <footer><a href="https://github.com/darkwood-com/darkwood">Source and contributions</a> · generated from tags.json schema v${escape(catalog.schemaVersion)}</footer>
</body>
</html>`;

await mkdir(output, { recursive: true });
await writeFile(resolve(output, "index.html"), html);
await writeFile(resolve(output, ".nojekyll"), "");
console.log(`Generated ${projects.length} cards in ${output}`);
