const form = document.querySelector("[data-filters]");

if (form) {
  const cards = [...document.querySelectorAll("[data-catalogue-groups] [data-card]")];
  const groups = [...document.querySelectorAll("[data-group]")];
  const resultCount = document.querySelector("[data-result-count]");
  const empty = document.querySelector("[data-empty]");
  const collator = new Intl.Collator("en", { numeric: true, sensitivity: "base" });
  const params = new URLSearchParams(location.search);

  for (const key of ["query", "kind", "status", "sort"]) {
    const control = form.elements.namedItem(key);
    if (control && params.has(key)) control.value = params.get(key);
  }

  const apply = () => {
    const data = Object.fromEntries(new FormData(form));
    const query = data.query.trim().toLocaleLowerCase();
    let visible = 0;

    for (const card of cards) {
      const matches = (!query || card.dataset.search.includes(query))
        && (!data.kind || card.dataset.kind === data.kind)
        && (!data.status || card.dataset.status === data.status);
      card.hidden = !matches;
      if (matches) visible += 1;
    }

    for (const group of groups) {
      const grid = group.querySelector(".card-grid");
      const ordered = [...grid.querySelectorAll("[data-card]")].sort((left, right) => {
        const comparison = collator.compare(left.dataset.name, right.dataset.name);
        return data.sort === "name-desc" ? -comparison : comparison;
      });
      for (const card of ordered) grid.append(card);
      const groupVisible = ordered.filter((card) => !card.hidden).length;
      group.hidden = groupVisible === 0;
      group.querySelector("[data-group-count]").textContent = groupVisible;
    }

    resultCount.textContent = visible;
    empty.hidden = visible !== 0;

    const next = new URLSearchParams();
    for (const key of ["query", "kind", "status", "sort"]) {
      if (data[key] && !(key === "sort" && data[key] === "name-asc")) next.set(key, data[key]);
    }
    history.replaceState(null, "", `${location.pathname}${next.size ? `?${next}` : ""}${location.hash}`);
  };

  form.addEventListener("input", apply);
  form.addEventListener("change", apply);
  form.addEventListener("reset", () => requestAnimationFrame(apply));
  apply();

  document.addEventListener("keydown", (event) => {
    if (event.key === "/" && document.activeElement?.tagName !== "INPUT") {
      event.preventDefault();
      form.elements.query.focus();
    }
    if (event.key === "Escape" && document.activeElement === form.elements.query) {
      form.elements.query.value = "";
      apply();
    }
  });
}

for (const link of document.querySelectorAll('a[href^="http"]')) {
  if (new URL(link.href).origin !== location.origin) link.rel = "noopener noreferrer";
}
