import assert from "node:assert/strict";
import test from "node:test";
import { validatePublicationDomains } from "./validate-publication-domains.mjs";

const manifest = (...domains) => ({ cards: domains.map((domain, index) => ({ id: `site-${index}`, pages: { domain } })) });

test("preserve root and existing single-label names; accept flattened names", () => {
  const names = ["darkwood.com", "pages.darkwood.com", "intent-content.darkwood.com", "ai-mcp-symfony-hackathon-content.darkwood.com"];
  assert.deepEqual(validatePublicationDomains(manifest(...names)), names);
  assert.deepEqual(validatePublicationDomains({ cards: [{ id: "library-without-website" }] }), []);
});

test("reject multi-label web names and technical DNS record names as Pages sites", () => {
  for (const name of ["intent.content.darkwood.com", "x.demo.apps.darkwood.com", "_submission._tcp.darkwood.com", "*.darkwood.com"]) {
    assert.throws(() => validatePublicationDomains(manifest(name)));
  }
});

test("enforce DNS syntax and the 63-byte label boundary", () => {
  validatePublicationDomains(manifest(`${"a".repeat(63)}.darkwood.com`));
  for (const name of [`${"a".repeat(64)}.darkwood.com`, "-demo.darkwood.com", "demo-.darkwood.com", "demo..darkwood.com", "https://demo.darkwood.com"]) {
    assert.throws(() => validatePublicationDomains(manifest(name)));
  }
});

test("reject case-insensitive collisions without renaming other parent domains", () => {
  assert.throws(() => validatePublicationDomains(manifest("demo-content.darkwood.com", "Demo-Content.darkwood.com")), /Duplicate/);
  assert.deepEqual(validatePublicationDomains(manifest("docs.example.org")), ["docs.example.org"]);
});
