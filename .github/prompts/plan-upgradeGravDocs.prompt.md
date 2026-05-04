# Upgrade Grav Docs to Learn Format

Upgrade the Grav CMS-formatted documentation for version **`{version}`** to the Learn site format. Apply format-only changes — **do not alter any content** (wording, code samples, structure).

Target directory: `app/pages/{version}/`

---

## Transformation Rules

### T1 — Frontmatter

Flatten Grav's nested frontmatter to the minimal Learn format:

```yaml
# Before
---
title: Page Title
metadata:
    description: Description here.
taxonomy:
    category: docs
---

# After
---
title: Page Title
description: Description here.
---
```

- `metadata.description` → promoted to top-level `description:`
- `taxonomy:` block → removed entirely
- Pages with no `metadata.description` (empty or missing value) → keep only `title:`, do **not** emit `description:` at all
- Do not add any other fields
- If the description value is wrapped in YAML double quotes (e.g. `"Some text."`), strip the surrounding quotes in the output — unless the value contains a colon (`:`), in which case keep the quotes for valid YAML
- If the description value is **not** quoted but **contains a colon** (`:`) in its text, wrap the entire value in double quotes to produce valid YAML (e.g. `description: "Some text: here."`)

> **Edge case:** When `metadata.description:` is present but empty (no value on the line), the regex must not consume the next YAML line. Always check that the extracted description value is non-empty before promoting it.
>
> **Implementation note:** When extracting the value after `description:`, use `[ \t]*` (spaces/tabs only) — **not** `\s*` — to avoid crossing newline boundaries. `\s*` will consume the newline and capture the next YAML line (e.g. `taxonomy:`) as the description value when the description is empty.

### T2 — Notice Blocks → GitHub Alerts

Convert all Grav `[notice]` shortcodes to GitHub-style alert blocks:

| Grav syntax | GitHub alert |
|---|---|
| `[notice]` | `> [!NOTE]` |
| `[notice=note]` | `> [!NOTE]` |
| `[notice=info]` | `> [!IMPORTANT]` |
| `[notice=tip]` | `> [!TIP]` |
| `[notice=warning]` | `> [!WARNING]` |

For multi-line notices, prefix **every content line** with `> `. Preserve all inline markdown (bold, links, code) verbatim.

```markdown
# Before
[notice=tip]
Line one.
Line two with **bold** and a [link](/page).
[/notice]

# After
> [!TIP]
> Line one.
> Line two with **bold** and a [link](/page).
```

> **Edge cases to handle:**
> - **Indented notices** (e.g. inside a numbered list): `   [notice=tip]...[/notice]` — match regardless of leading whitespace, not just at line start. Move the converted alert block *outside* the list item (alerts cannot be nested in list items).
> - **Inline notices** (opening tag mid-sentence): e.g. `Some text. [notice]Warning.[/notice]` — split the sentence before the tag, then output the alert on its own line.
> - **Notices inside HTML comments** (`<!-- ... -->`): do **not** convert them. Some pages use HTML comments to temporarily disable content; converting notices inside them would corrupt the comment. Detect open comment ranges before processing and skip any notice match whose start position falls within a comment range.

### T3 — Image `?resize=` Parameters

Remove Grav's image processing query parameters from all image references:

```markdown
# Before
![Alt text](/images/file.png?resize=500)
![Alt text](/images/file.png?resize=800,600)

# After
![Alt text](/images/file.png)
```

### T4 — Modular Content Inlining

Replace every `[plugin:content-inject]` call based on which modular file it references:

**Banner modular files → frontmatter flags** (do NOT inline content):

| Modular file | Action |
|---|---|
| `modular/_update5.0` | Remove inject line, add `outdated: true` to the page's frontmatter |
| `modular/_updateRequired` | Remove inject line, add `obsolete: true` to the page's frontmatter |

```yaml
# Before — frontmatter of injecting page
---
title: Some Page
---
[plugin:content-inject](/modular/_updateRequired)

# After
---
title: Some Page
obsolete: true
---
```

**Other modular files → inline verbatim** (copy content in place of the inject line):

```markdown
# Before
[plugin:content-inject](/path/to/other-modular)

# After
(full contents of other-modular/docs.md pasted here)
```

> **Implementation note:** Modular files often contain backslash sequences (e.g. `\wsl$`, `\h`). When substituting content via regex, **never pass the modular content as a string replacement template** — regex engines interpret `\n`, `\1`, `\h`, etc. as special sequences and will throw an error or silently corrupt the content. Always use a **literal replacement function** (e.g. a lambda that returns the string directly) so the content is treated as a plain string.

The modular files themselves are deleted after all injections are resolved (see Phase 6).

### T5 — Misc Grav Syntax

Strip any remaining Grav-only wrapper tags, keeping only the inner text:

- `[size=N]Text[/size]` → `Text`
- Check for any other unknown shortcodes not covered above and handle similarly

### T6 — Link and Image Path Normalization

**Image paths** must be absolute (start with `/images/`). Relative image paths must be fixed:

```markdown
# Before
![Alt text](images/screenshot.png)
![Alt text](../images/screenshot.png)

# After
![Alt text](/images/screenshot.png)
```

**Internal page links** must not contain numeric folder prefixes or version numbers:

```markdown
# Before
[Requirements](04.installation/01.requirements)
[Requirements](installation/requirements)      ← missing leading /

# After
[Requirements](/installation/requirements)
```

Rules:
- Add leading `/` if missing
- Strip numeric prefixes from each path segment (`04.installation` → `installation`, `01.requirements` → `requirements`)
- Do not modify external links (starting with `http://` or `https://`) or anchor-only links (`#section`)

---

## Implementation Strategy

**Always implement phases 1–5b as a single Python script** that iterates over all `.md` files in one pass. Manual file-by-file editing across 150+ files is error-prone and non-repeatable. A script also guarantees:
- All transformations are applied atomically and in the correct order within each file
- The inlining lambda workaround (T4) is easy to apply
- The run can be reset via `git checkout -- app/pages/{version}/` and rerun after any fix

Save the script alongside the pages (e.g. `_meta/upgrade_grav_{version}.py`) and delete it after the upgrade is verified.

---

## Execution Phases

### Phase 0 — Discovery (run before everything else)

Scan `app/pages/{version}/` to build a version-specific inventory:

```bash
# All modular files to inline
find app/pages/{version} -path "*/_modular*" -name "*.md"
find app/pages/{version}/modular -name "*.md" 2>/dev/null

# All injection points
grep -rn "plugin:content-inject" app/pages/{version}/

# All image resize parameters (use -F for literal ? matching)
grep -Frn '?resize=' app/pages/{version}/

# All notice blocks
grep -rn '\[notice' app/pages/{version}/

# Any other unknown Grav shortcodes
grep -rn '\[size=' app/pages/{version}/
grep -rEn '\[[a-z]+=' app/pages/{version}/ | grep -v 'notice\|size\|plugin'

# Relative image paths (missing leading /) — use single quotes to avoid bash ! history expansion
grep -rEn '!\[.*\]\((\.\./)?images/' app/pages/{version}/

# Internal links with numeric prefixes
grep -rEn '\]\([^)]*[0-9]+\.[a-z]' app/pages/{version}/ | grep -v 'http'

# Internal links missing leading /
grep -rEn '\]\([a-z]' app/pages/{version}/ | grep -v 'http'
```

Report findings before proceeding. If any unknown shortcode patterns are found, flag them and wait for instructions.

### Phase 1 — Inline Modular Content (T4)

For each injection point found in Phase 0:
1. Identify which modular file is referenced
2. If it's `modular/_update5.0` → remove the inject line and add `outdated: true` to the page frontmatter
3. If it's `modular/_updateRequired` → remove the inject line and add `obsolete: true` to the page frontmatter
4. For all other modular files → read the file's full content and paste it verbatim in place of the inject line
5. Confirm all injection points are resolved

### Phase 2–5b — Apply All Remaining Transformations (T1–T6)

In the same single script pass that processes each file, apply all remaining transformations in this order:

1. **T2 — Notice conversion**: Convert all `[notice...]` blocks. Runs after Phase 1 so inlined content is covered.
2. **T1 — Frontmatter upgrade**: Flatten `metadata.description`, remove `taxonomy:`. Remember `[ \t]*` not `\s*` when parsing the description value.
3. **T3 — Image resize**: Remove `?resize=` query parameters.
4. **T5 — Misc syntax**: Strip `[size=N]...[/size]` and any other unknown shortcodes.
5. **T6 — Link normalization**: Fix relative image paths and internal links (leading `/`, numeric prefixes).

### Phase 6 — Delete Modular Folders

After Phase 1 is fully complete, delete:
- `app/pages/{version}/modular/` (if it exists)
- All `_modular/` subfolders within `app/pages/{version}/`

---

## Verification

Run these checks after all phases — each should return no results:

```bash
grep -rn '\[notice' app/pages/{version}/
grep -rn 'plugin:content-inject' app/pages/{version}/
grep -rn 'taxonomy:' app/pages/{version}/
grep -rn '^    description:' app/pages/{version}/   # catches un-flattened metadata.description
grep -Frn '?resize=' app/pages/{version}/          # -F for literal ? matching
grep -rn '\[size=' app/pages/{version}/
```

> **Note on the `[notice` check:** Results that appear inside HTML comment blocks (`<!-- ... -->`) are **expected and acceptable** — those notices were intentionally commented out in the source and must not be converted. Confirm any hits are inside `<!--` … `-->` before treating them as failures.

```bash
# No relative image paths — single quotes avoid bash ! history expansion
grep -rEn '!\[.*\]\((\.\./)?images/' app/pages/{version}/

# No internal links with numeric prefixes
grep -rEn '\]\([^)]*[0-9]+\.[a-z]' app/pages/{version}/ | grep -v 'http'

# No internal links missing leading /
grep -rEn '\]\([a-z]' app/pages/{version}/ | grep -v 'http'
```

Also verify:
- Modular folders no longer exist under `app/pages/{version}/`
- No `description: taxonomy:` values (empty-description regex bleed)
- No unquoted `description:` values containing a colon — run:
  ```bash
  # Descriptions whose value (after the key) contains a colon and is not quoted
  grep -rn '^description: [^"].*:' app/pages/{version}/
  ```
- Spot-check 5–6 converted pages for correct frontmatter, alert rendering, and working links in the dev server
