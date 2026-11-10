concise summary of what omer-quotes/var/app contains and what it implies for the analysis.

### Pipeline coverage
- **30 repositories** (one JSON file per repo in each collection), aligned with `data/repos.json`.
- **Ingestion**: all **30** show `status: "ok"` (no failed ingest rows in `ingestion/`).
- **Local clones**: **30** directories under **`ingested/`** (~**136 MB** total—mostly full git checkouts, not the small JSON sidecars).

### Stored layers (per repo)
| Area | Role |
|------|------|
| **`documents/`** | Canonical text: `id`, `fullName`, `canonicalText` (metadata + README/docs snippets). |
| **`corpus/`** | Normalized string + **token list** after cleaning/stopwords/light stem. |
| **`vectors/`** | Per-repo **TF–IDF weights**; **`_meta.json`** holds corpus-wide **DF** and **IDF**. |
| **`classification/`** | Heuristic **mixed** label + score maps (base + word-class path). |
| **`clustering/`** | **Cluster id** (0–2) per repo. |
| **`ingestion/`** | Ingest status per repo. |
| **`reports/`** | Run reports + e.g. **`comparison_latest.json`** (Messenger vs Flow step/error counts). |

### Corpus / feature stats (from your current run)
- **Vocabulary (DF terms)** in `_meta.json`: **1086** distinct tokens.
- **Tokens per document** (after preprocessing): **min 4**, **max 1418**, **median 6**, **mean ~145** (a few repos dominate length; most are short).
- **Canonical text length** (chars): **min 21**, **max ~11.6k**, **mean ~1.3k**.

### Classification snapshot
- **Labels** (heuristic: `web` / `game` / `misc`): **1 × `web`**, **29 × `misc`** in this snapshot.  
  That matches the heuristic: only strong name/slug hits (e.g. **Symfony**) tend to land in **`web`**; most repo names do not trigger **`game`** or **`web`**.

### Clustering snapshot
- **3 clusters**, **10 repos each** (0, 1, 2). That is the **deterministic MVP assignment** (index-based modulo), not a data-driven k-means split.

### Orchestration comparison artifact
- **`reports/comparison_latest.json`**: **5** steps and **0** errors for both **messenger** and **flow** (same pipeline shape in this run).

**Bottom line:** `var/app` is a **complete, inspectable trace** of: ingest → text → tokens → TF–IDF → similarity-ready vectors → heuristic labels → fixed-size clusters, with **comparable orchestration reports** on top. The **main analytical signal** is in **`vectors/_meta.json`** (global DF/IDF) and per-repo **`vectors/*.json`**; **classification/clustering** are intentionally **lightweight** and skew **misc** / **balanced clusters** unless you tighten heuristics or clustering.
