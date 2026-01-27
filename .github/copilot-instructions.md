# Copilot Instructions for UserFrosting Learn

## Project Overview
**PRIMARY GOAL: This repository exists to document UserFrosting 6 (the PHP framework), NOT to document this documentation site itself.**

The source code to be documented is at [userfrosting/monorepo](https://github.com/userfrosting/monorepo). When users ask to improve, proofread, or create documentation, they are referring to documenting UserFrosting 6 features, configuration, and usage - not the documentation site's infrastructure.

This project (UserFrosting Learn) is simply the **infrastructure** that displays the documentation. It's built with **UserFrosting 6** and serves versioned documentation pages written in Markdown (in `app/pages/{version}/`). It's served publicly at [https://learn6.userfrosting.com](https://learn6.userfrosting.com).

## Documentation Guidelines
**CRITICAL: The documentation content documents UserFrosting (from [userfrosting/monorepo](https://github.com/userfrosting/monorepo)), NOT this documentation site.**

When users request documentation work (improving, proofreading, creating pages), they mean:
- ✅ Documenting UserFrosting 6 features, APIs, configuration, and usage
- ✅ Verifying code examples against the [UserFrosting monorepo](https://github.com/userfrosting/monorepo)
- ✅ Making sure example commands, code snippets, and configurations match UserFrosting 6 behavior and not other versions
- ✅ Ensuring images and links in the documentation point to correct resources in UserFrosting
- ✅ Proofreading and improving clarity of documentation about UserFrosting
- ✅ Verifying images point to existing files with correct absolute paths
- ✅ Verifying links point to existing pages with correct absolute paths. Renamed pages should have links updated accordingly.
- ✅ Verifying external links are still valid
- ✅ Ensuring terminology is consistent with UserFrosting branding and naming conventions
- ✅ Ensuring technical accuracy of all external technical content
They do NOT mean:
- ❌ NOT documenting how this documentation site works internally

This repository's PHP/TypeScript code is ONLY for rendering the documentation (routing, caching, navigation). Only reference this repo's code when:
- Fixing bugs in the documentation site itself
- Modifying how pages are rendered
- Working on the site's infrastructure

### Documentation Content
For all documentation tasks, reference the UserFrosting monorepo for accurate technical details, code samples, and feature explanations. Pages should be referenced between each other content wise. Consider other pages as part of the same context.

When editing the documentation markdown files, references to code samples only to the relevant version (e.g., `6.0`), and explain features as they exist in that version. The only exception is in the upgrade guide. When reviewing documentation, ensure that code snippets are accurate and up-to-date against the actual UserFrosting codebase.

Suggest improvements to clarity, grammar, and formatting as needed, but do not change technical content unless verifying against the UserFrosting monorepo. Suggest adding new documentation pages or content only when there is a clear gap in coverage of UserFrosting features or concepts.

You may search the web for relevant external resources to link to, but ensure they are reputable and accurate. Always prioritize official documentation from third-party libraries or tools used by UserFrosting. You may also use other resources as example and inspiration for writing style, but the content must be original and specific to UserFrosting, otherwise proper source must be cited.

### Identifying Obsolete Documentation
**CRITICAL: Always check for the `obsolete: true` flag in page frontmatter.**

When reviewing or editing documentation pages:
1. **Check the frontmatter** for `obsolete: true` - this indicates the page documents outdated features or practices
2. **Verify against the monorepo** - Check the actual implementation in [userfrosting/monorepo](https://github.com/userfrosting/monorepo) to see what the current approach is
3. **Alert the user** if entire chapters or sections are obsolete and need complete rewrites (e.g., Webpack Encore documentation when UF6 uses Vite)
4. **Cross-reference** with this documentation site's own implementation (e.g., check `vite.config.ts`, `package.json`) to understand what UF6 actually uses
5. **Major gaps** - If you discover major documentation gaps (obsolete chapters, missing critical features, outdated architecture), inform the user immediately

Examples of checks to perform:
- If a page mentions Webpack, verify UF6 doesn't use Vite instead
- If a page documents a CLI command, check the actual command exists in the monorepo
- If a page explains configuration, verify the config structure matches the monorepo
- If a page documents an API, verify it exists and hasn't changed in UF6

When you encounter `obsolete: true` pages, inform the user that the content is outdated and may need to be rewritten to match current UF6 implementation.

### Documentation Style
The documentation tone should be casual, clear, fun and aimed at PHP developers of varying experience levels, but mostly novices and non-developers. The tone should be educational. UserFrosting is designed to bring new and returning developers up to speed with the modern PHP community. It should focus on learning best practices and coding first, and only teach the user how to use UserFrosting to build their project as a second step. It should do so with practical and fun examples. The end goal is to educate and empower users. 

The documentation **SHOULD** avoid jargon and explain concepts in simple terms. Use analogies and examples where appropriate to clarify complex topics.

The documentation **SHOULD** is meant to be read sequentially, with each chapter building on the previous ones. However, individual pages should also be understandable on their own.

It should link to relevant external resources where appropriate.

UserFrosting should always be referred to in the documentation as "UserFrosting" (with proper case) or "UF", never "UF6" or "UserFrosting 6" except when specifically discussing version differences.

Markdown syntax supports special alert blocks (e.g., `> [!NOTE]`, `> [!TIP]`, etc.) via a custom CommonMark extension. 

All code snippets should use triple backticks with language specified for syntax highlighting (e.g., ```php, ```bash, ```ts).

### Images and Links

**CRITICAL - Image Paths**: Images are stored in `app/pages/{version}/images` alongside the markdown files, and **MUST always use absolute paths starting with `/`**. 
- ✅ Correct: `![Alt text](/images/screenshot.png)`
- ❌ Wrong: `![Alt text](images/screenshot.png)` or `![Alt text](../images/screenshot.png)`
Images should be checked to ensure the file exists at the specified path.

Internal links should use absolute paths without version numbers to allow automatic version switching.
- ✅ Correct: `[Requirements](/installation/requirements)`
- ❌ Wrong: `[Requirements](installation/requirements)` or `[Requirements](/04.installation/01.requirements)`

Pages have anchor links for sections (e.g., `#motivation`). Ensure links point to existing sections.

### Chapters and Pages
Documentation pages are stored in `docs.md` or `chapter.md` files under `app/pages/{version}/` (e.g., `app/pages/6.0/01.quick-start/docs.md`).

`docs.md` files are standalone documentation pages, while `chapter.md` files represent chapters that may contain multiple sections or sub-pages. Chapters are displayed with their own landing page summarizing the chapter content. 

**Chapter Structure Best Practice:**
Chapter content should follow this pattern for maximum clarity:
1. Start with `#### Chapter N` and `# Chapter Title` headings
2. Provide a brief contextual introduction explaining the problem domain or why the topic matters
3. Introduce the solution or approach UserFrosting uses
4. Conclude with what the chapter will cover

This problem → solution structure helps readers understand the "why" before diving into the "how". For example, the Asset Management chapter first explains the challenges of serving assets (locating files, URL generation, bundling, framework integration, dependencies), then introduces Vite as the solution, and finally outlines the chapter contents.

Chapters **MUST** contain:
- A level 4 heading with the chapter number (`#### Chapter 13`)  
- A top-level heading with the chapter title (`# Asset Management`)
- Descriptive content explaining the chapter's purpose and scope

Folder names control the breadcrumb and tree hierarchy in the sidebar navigation. Numeric prefixes (e.g., `01.`) control ordering of pages in the navigation.

When adding new pages, ensure they are placed in the correct folder structure to reflect their logical grouping and order. Update any relevant navigation or links to include the new pages. Update numbering if required.

### Pages Frontmatter
Each documentation page has YAML frontmatter at the top with metadata. Common fields include:
- `title`: The page title displayed in the header and navigation
- `description`: A short description of the page content (direct field, not nested under `metadata`)
- `obsolete`: (boolean) Whether the page contains obsolete documentation, usually from a previous version, that requires updating
- `wip`: (boolean) Whether the page is a work in progress and not yet complete. Compared to "obsolete", this indicates the page is valid for the current version, but is actively being worked on.
- `tags`: (array) Optional tags displayed to the user for categorization
- `keywords`: (array) Optional keywords for search optimization

When creating or editing pages, ensure the frontmatter is accurate and complete. 

Frontmatter **SHOULD NOT** include any other fields beyond those documented here, especially the outdated `taxonomy` and `metadata` fields.

**Example (docs.md page):**
```yaml
---
title: Getting Started
description: Learn how to set up Vite for asset management in UserFrosting.
---, images, fonts, and other resources — create the client-side experience of your web application. When dealing with assets on the server, our application needs to address some problems:

1. How do we locate an assets that's usually not located in the publicly served folder and **generate an appropriate URL** proxy so it can be accessed publicly?
2. When the client actually loads a page and **requests** an asset via the URL, how do we map the URL back to a file path on the server and return it to the client?
3. How do we handle compiled assets and bundle assets together to improve efficiency?
4. How do we integrate with modern frameworks, like Vue.JS, or preprocessors like Less or Sass? 
5. How to we load external NPM dependencies?

To answer this, UserFrosting uses **Vite** as its default asset bundler to manage, compile, and optimize these resources efficiently.

Vite provides lightning-fast development with Hot Module Replacement (HMR), instant server start, and optimized production builds. It offers native support for modern web technologies including TypeScript, Vue 3, CSS preprocessors, and ESM modules.

This chapter covers everything you need to know about managing assets in UserFrosting, from basic setup to advanced optimization techniques.
```

**Example (chapter.md page):**
```yaml
---
title: Asset Management
description: Learn how to manage, compile, and optimize frontend assets in UserFrosting using Vite.
---

#### Chapter 13

# Asset Management

Frontend assets — JavaScript, TypeScript, CSS, Vue components... [content continues]
```

## Project-Specific Patterns
Use these patterns when working on the documentation site codebase only (itself, not the documentation content).:

### Backend
Built with **UserFrosting 6** framework:
- **Entry**: `app/src/MyRoutes.php` - Slim route definitions
- **Key Service**: `DocumentationRepository` (`app/src/Documentation/`) - core logic for page retrieval, versioning, caching, and navigation generation
- **Controller**: `DocumentationController` - renders pages via Twig templates
- **Sprinkle Pattern**: `Recipe.php` and `CoreRecipe.php` handle DI, event listeners, and service registration
- **Twig Templates**: `app/templates/pages/*.html.twig`

### Frontend (TypeScript/Vue 3)
- **Build Tool**: Vite (not Webpack)
- **Entry**: `app/assets/main.ts` - imports theme (LESS), highlights, UIkit
- **Styling**: LESS preprocessor with UIkit 3 framework
- **Config**: `vite.config.ts` handles asset optimization (exclude sprinkles from pre-bundling, optimize UIkit)
- **Tests**: Vitest + Vue Test Utils
- **No Pinia/reactive state** currently needed - mostly static documentation site

### Documentation Page Structure
Pages are markdown files under `app/pages/{version}/` (e.g., `app/pages/6.0/01.quick-start/docs.md`). 
- **Folder names** control breadcrumb/tree hierarchy
- Numeric prefixes (e.g., `01.`) control ordering in navigation
- `DocumentationRepository::getTree()` parses file structure into nested menu

### Content Rendering Flow
1. Route: `GET /6.0/quick-start` → `DocumentationController::pageVersioned()`
2. `DocumentationRepository::getPage()` finds markdown, validates version, caches result
3. Twig template renders with `page`, `breadcrumbs`, `previousPage`, `nextPage`
4. Frontend assets (Vite manifest) injected via PHP

### Markdown Processing
- Custom CommonMark extension for alerts (GitHub-style: `> [!NOTE]`, etc.)
- Markdown parsed to HTML in PHP backend (via Twig filter or service)

### Versioning
- Pages stored per-version folder (`6.0/`, `5.1/`, etc.)
- `VersionValidator` enforces valid version format
- Fallback mechanism if version doesn't exist
- Cache key includes version to prevent cross-version contamination

### File Organization
```
app/
  src/                     # PHP source (PSR-4: UserFrosting\Learn\)
    Documentation/         # Core repository/page logic
    Controller/            # HTTP handlers
    Bakery/               # CLI commands & listeners
    Middleware/           # Twig globals, etc.
    Twig/Extensions/      # Custom Twig filters
  templates/              # Twig view files
  pages/                  # Markdown documentation
    6.0/
      01.quick-start/
        docs.md
  assets/                 # Frontend (Vite root)
    main.ts               # Entry point
    theme.less            # Global styling
    css/                  # Additional styles
    public/               # Static assets
```

### Developer Workflows

#### Run Locally (without Docker)
```bash
# Terminal 1: Backend
php bakery serve  # Runs on http://localhost:8080 (default)

# Terminal 2: Frontend dev server
npm run vite:dev   # Runs on http://localhost:5173
```
Browser loads via Slim/PHP, which references Vite-compiled assets.

#### Run with Docker
```bash
docker-compose up -d
# App on http://localhost:8080
```

#### Build & Test
```bash
# Frontend
npm run vite:build      # Production build
npm run typecheck       # Vue-tsc type checking
npm run lint            # ESLint (auto-fix)
npm run test            # Vitest unit tests
npm run coverage        # Coverage report → _meta/coverage/

# Backend
vendor/bin/phpunit      # Unit tests (runs in UF_MODE=testing)
vendor/bin/phpstan      # Static analysis
vendor/bin/php-cs-fixer # Code formatting
```

#### Important Tasks
Run via VS Code command palette or terminal:
- `Bakery - Serve` / `Frontend - Vite Dev` (background tasks)
- `==> Serve` (runs both together)
- `Backend - PHPUnit`, `Frontend - Tests`

#### Testing & QA
- Unit tests in `app/tests/` (using Vitest)
- Coverage stored in `_meta/coverage.xml` (Codecov)
- Type checking required before build: `npm run typecheck`
- Test mode: `UF_MODE=testing` env var

#### CI/CD
- GitHub Actions workflow (Build.yml)
- Codecov integration for coverage tracking

## Common Pitfalls

1. **Verify against source code**: The documentation is FOR UserFrosting 6 (monorepo), NOT for this documentation site. Always verify technical details against the [UserFrosting monorepo](https://github.com/userfrosting/monorepo), not this repo's code. This repo is only the rendering infrastructure. Use semantic search or GitHub repo search on the monorepo to find current implementations.
2. **Obsolete documentation**: ALWAYS check page frontmatter for `obsolete: true`. Pages marked obsolete document outdated features and need verification/rewriting against current UF6 implementation in the monorepo. If you find obsolete pages during review, alert the user immediately about the need for updates.
3. **Image paths in documentation**: ALWAYS use absolute paths starting with `/` (e.g., `/images/file.png`), NEVER relative paths (e.g., `images/file.png`). This is required for proper routing across versions.
4. **Version-specific features**: Ensure that any features, commands, or configurations mentioned correspond to the correct UserFrosting version being documented. Features may differ between major versions.
5. **Consistent terminology**: Always refer to the software as "UserFrosting" or "UF". Avoid using "UF6" or "UserFrosting 6" except when specifically discussing version differences.
