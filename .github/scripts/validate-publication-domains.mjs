import { readFile } from "node:fs/promises";
import { resolve } from "node:path";
import { pathToFileURL } from "node:url";

export function validatePublicationDomains(manifest) {
  const seen = new Set();
  const domains = [];
  for (const card of manifest.cards ?? []) {
    if (!card.pages) continue;
    const domain = card.pages.domain;
    if (typeof domain !== "string" || domain.length > 253 ||
        !domain.split(".").every(label => /^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i.test(label))) {
      throw new Error(`Invalid DNS hostname for ${card.id}`);
    }
    const normalized = domain.toLowerCase();
    if (seen.has(normalized)) throw new Error(`Duplicate publication hostname: ${normalized}`);
    if (normalized.endsWith(".darkwood.com") && normalized.split(".").length !== 3) {
      throw new Error(`Use one label below darkwood.com for ${card.id}: <name-function>-<category>.darkwood.com`);
    }
    seen.add(normalized);
    domains.push(domain);
  }
  return domains;
}

if (process.argv[1] && import.meta.url === pathToFileURL(resolve(process.argv[1])).href) {
  const manifestPath = resolve(process.argv[2] ?? resolve(import.meta.dirname, "../distribution-cards.json"));
  const domains = validatePublicationDomains(JSON.parse(await readFile(manifestPath, "utf8")));
  console.log(`Validated ${domains.length} publication hostnames: DNS label lengths, uniqueness and the Darkwood single-label convention.`);
}
