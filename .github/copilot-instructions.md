# Copilot Instructions for UserFrosting Learn

## Project Overview
UserFrosting Learn is the official documentation website for **UserFrosting 6** (https://github.com/userfrosting/monorepo), also known as "UF" or "UF6". Built with **UserFrosting 6**, it serves versioned documentation written in Markdown across multiple versions (e.g., `6.0/`) with dynamic page rendering, breadcrumbs, and image serving. It's served publicly at [https://learn6.userfrosting.com](https://learn6.userfrosting.com).

## Architecture

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

## Critical Developer Workflows

### Run Locally (without Docker)
```bash
# Terminal 1: Backend
php bakery serve  # Runs on http://localhost:8000

# Terminal 2: Frontend dev server
npm run vite:dev   # Runs on http://localhost:5173
```
Browser loads via Slim/PHP, which references Vite-compiled assets.

### Run with Docker
```bash
docker-compose up -d
# App on http://localhost:8080
```

### Build & Test
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

### Important Tasks
Run via VS Code command palette or terminal:
- `Bakery - Serve` / `Frontend - Vite Dev` (background tasks)
- `==> Serve` (runs both together)
- `Backend - PHPUnit`, `Frontend - Tests`

### Documentation Generation
The actual documentation content is for UserFrosting's main repository, the [UserFrosting monorepo](https://github.com/userfrosting/monorepo), not in this repository. When editing the documentation markdown files, references to code samples should point to that repository, refer only to the relevant version (e.g., `6.0`), and explain features as they exist in that version. When reviewing documentation, ensure that code snippets are accurate and up-to-date against the actual UserFrosting codebase.

The documentation site here simply renders those markdown files into HTML pages with navigation, versioning, and styling.

The documentation tone should be casual, clear, and aimed at PHP developers of varying experience levels, but mostly novices and non-developers. It should focus on practical usage, configuration, and extension of UserFrosting, with examples and best practices. It should link to relevant external resources where appropriate.

UserFrosting should always be referred to in the documentation as "UserFrosting" (with proper case) or "UF", never "UF6" or "UserFrosting 6" except when specifically discussing version differences.

Markdown syntax supports special alert blocks (e.g., `> [!NOTE]`, `> [!TIP]`, etc.) via a custom CommonMark extension. 

**CRITICAL - Image Paths**: Images are stored in `app/pages/{version}/images` alongside the markdown files, and **MUST always use absolute paths starting with `/`**. 
- ✅ Correct: `![Alt text](/images/screenshot.png)`
- ❌ Wrong: `![Alt text](images/screenshot.png)` or `![Alt text](../images/screenshot.png)`
Images should be checked to ensure the file exists at the specified path.

Internal links should use absolute paths without version numbers to allow automatic version switching.

## Project-Specific Patterns

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

## Key Integration Points

### UserFrosting Framework
- Uses **Sprinkle pattern** (plugin architecture)
- `Recipe.php` registers routes, event listeners, Twig extensions
- DI container configuration in `ServicesProvider/`
- Events: `BakeCommandEvent`, `SetupCommandEvent`, etc.

### Asset Pipeline
- Vite builds to `public/assets/` with manifest
- PHP reads manifest to inject versioned asset paths into Twig
- LESS processed by Vite (not node-sass)
- UIkit 3 as UI framework (not Bootstrap)

### Markdown Processing
- Custom CommonMark extension for alerts (GitHub-style: `> [!NOTE]`, etc.)
- Markdown parsed to HTML in PHP backend (via Twig filter or service)

## Testing & QA

### Frontend
- Unit tests in `app/tests/` (using Vitest)
- Coverage stored in `_meta/coverage.xml` (Codecov)
- Type checking required before build: `npm run typecheck`

### Backend
- PHPUnit tests in `app/tests/` (PSR-4: `UserFrosting\Tests\Learn\`)
- PHPStan level configured in `phpstan.neon`
- Test mode: `UF_MODE=testing` env var

### CI/CD
- GitHub Actions workflow (Build.yml)
- Codecov integration for coverage tracking

## Common Pitfalls

1. **Image paths in documentation**: ALWAYS use absolute paths starting with `/` (e.g., `/images/file.png`), NEVER relative paths (e.g., `images/file.png`). This is required for proper routing across versions.
2. **Vite asset paths**: Always use `base: /assets/` in vite.config.ts
3. **UIkit imports**: Must import Icons separately; don't exclude UIkit from optimizeDeps
4. **Sprinkle dependencies**: Ensure `@userfrosting/sprinkle-core` not pre-bundled (in excludeOptimizeDeps)
5. **Page versioning**: Validate version format before querying cache
6. **Twig global context**: `TwigGlobals` middleware adds context; check what's available in templates
