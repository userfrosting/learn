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

## Execution Phases

### Phase 0 — Discovery (run before everything else)

Scan `app/pages/{version}/` to build a version-specific inventory:

```bash
# All modular files to inline
find app/pages/{version} -path "*/_modular*" -name "*.md"
find app/pages/{version}/modular -name "*.md" 2>/dev/null

# All injection points
grep -rn "plugin:content-inject" app/pages/{version}/

# All image resize parameters
grep -rn "?resize=" app/pages/{version}/

# All notice blocks
grep -rn "\[notice" app/pages/{version}/

# Any other unknown Grav shortcodes
grep -rn "\[size=" app/pages/{version}/
grep -rEn "\[[a-z]+=?" app/pages/{version}/ | grep -v "^\[!" | grep -v "notice\|size\|plugin"

# Relative image paths (missing leading /)
grep -rEn "!\[.*\]\((\.\.?/)?images/" app/pages/{version}/

# Internal links with numeric prefixes
grep -rEn "\]\([^)]*[0-9]+\.[a-z]" app/pages/{version}/ | grep -v "http"

# Internal links missing leading /
grep -rEn "\]\([a-z]" app/pages/{version}/ | grep -v "http"
```

Report findings before proceeding. If any unknown shortcode patterns are found, flag them and wait for instructions.

### Phase 1 — Inline Modular Content (T4)

For each injection point found in Phase 0:
1. Identify which modular file is referenced
2. If it's `modular/_update5.0` → remove the inject line and add `outdated: true` to the page frontmatter
3. If it's `modular/_updateRequired` → remove the inject line and add `obsolete: true` to the page frontmatter
4. For all other modular files → read the file's full content and paste it verbatim in place of the inject line
5. Confirm all injection points are resolved

### Phase 2 — Notice Conversion (T2)

Convert all `[notice...]` blocks across all files using the T2 mapping table. Run *after* Phase 1 so inlined content is also covered.

### Phase 3 — Frontmatter Upgrade (T1)

Flatten `metadata.description` and remove `taxonomy:` blocks across all files. *Can run in parallel with Phase 2.*

After running, verify no files contain `description: taxonomy:` (sign of empty-description regex bleed) and no unquoted descriptions containing a colon:

```bash
grep -rn "description: taxonomy:" app/pages/{version}/
grep -rn '^description:' app/pages/{version}/ | grep ':' | grep -v 'description: "'
```

### Phase 4 — Image Path Cleanup (T3)

Remove `?resize=` parameters from all files identified in Phase 0. *Can run in parallel with Phase 2.*

### Phase 5 — Misc Syntax Cleanup (T5)

Strip any remaining Grav-only wrapper tags identified in Phase 0. *Can run in parallel with Phase 2.*

### Phase 5b — Link and Image Path Normalization (T6)

For all files with issues identified in Phase 0:
1. Fix relative image paths → absolute paths starting with `/images/`
2. Strip numeric prefixes from internal link path segments
3. Add leading `/` to internal links that are missing it
4. Do not touch external links or anchor-only links

*Can run in parallel with Phase 2.*

### Phase 6 — Delete Modular Folders

After Phase 1 is fully complete, delete:
- `app/pages/{version}/modular/` (if it exists)
- All `_modular/` subfolders within `app/pages/{version}/`

---

## Verification

Run these checks after all phases — each should return no results:

```bash
grep -rn "\[notice" app/pages/{version}/
grep -rn "plugin:content-inject" app/pages/{version}/
grep -rn "taxonomy:" app/pages/{version}/
grep -rn "^    description:" app/pages/{version}/   # catches un-flattened metadata.description
grep -rn "?resize=" app/pages/{version}/
grep -rn "\[size=" app/pages/{version}/
```

```bash
# No relative image paths
grep -rEn "!\[.*\]\((\.\.?/)?images/" app/pages/{version}/

# No internal links with numeric prefixes
grep -rEn "\]\([^)]*[0-9]+\.[a-z]" app/pages/{version}/ | grep -v "http"

# No internal links missing leading /
grep -rEn "\]\([a-z]" app/pages/{version}/ | grep -v "http"
```

Also verify:
- Modular folders no longer exist under `app/pages/{version}/`
- No `description: taxonomy:` values (empty-description regex bleed)
- No unquoted `description:` values containing a colon — run:
  ```bash
  # Descriptions with colons that are not wrapped in double quotes
  grep -rn '^description:' app/pages/{version}/ | grep ':' | grep -v 'description: "'
  ```
- Spot-check 5–6 converted pages for correct frontmatter, alert rendering, and working links in the dev server
