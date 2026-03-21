---
title: Build Workflow
description: Learn how to use NPM scripts and Bakery commands to run, test, and build frontend assets in UserFrosting.
---

## NPM Scripts: Your Command Toolbox

The `scripts` section in `package.json` is one of its most powerful features. Scripts are custom commands that automate common tasks and save you from typing long, repetitive commands.

NPM scripts are shell commands defined in your `package.json` that you can run using `npm run <script-name>`. According to the [NPM scripts documentation](https://docs.npmjs.com/cli/v10/using-npm/scripts), scripts can:

1. Run command-line tools installed in `node_modules/.bin/`
2. Chain multiple commands together
3. Pass arguments and environment variables
4. Execute in a cross-platform compatible way

### Why Use Scripts?

Instead of remembering complex commands like:

```bash
./node_modules/.bin/vite build --mode production --config vite.config.ts
```

You can simply run:

```bash
npm run vite:build
```

This makes your workflow:
- **Consistent** - Everyone uses the same commands
- **Documented** - New team members can see available commands
- **Portable** - Works across different operating systems
- **Maintainable** - Update the command once, and everyone benefits

### UserFrosting's Script Commands

Let's explore each script in detail:

#### Development Scripts

**`npm run vite:dev`** - Starts the Vite development server
```json
"vite:dev": "vite"
```

This launches Vite in development mode with:
- **Hot Module Replacement (HMR)** - Updates your browser instantly when you save files
- **Fast refresh** - Preserves component state during updates
- **Source maps** - Maps compiled code to source code for easier debugging
- **Development server** - Runs on `http://localhost:5173` by default

When running, your browser loads assets directly from Vite's dev server for the fastest possible feedback loop.

**`npm run vite:build`** - Creates an optimized production build
```json
"vite:build": "vite build"
```

This generates production-ready assets with:
- **Code minification** - Removes whitespace and shortens variable names
- **Tree shaking** - Eliminates unused code from bundles
- **Code splitting** - Breaks code into smaller chunks for faster loading
- **Cache busting** - Adds hashes to filenames (for example, `main-a1b2c3d4.js`)

Output goes to `public/assets/` and includes a manifest file for UserFrosting to reference assets.

> [!TIP]
> Both commands can also be run from Bakery for a more integrated experience. See below for details.

#### Quality Assurance Scripts

**`npm run typecheck`** - Type-checks TypeScript without compilation
```json
"typecheck": "vue-tsc --noEmit"
```

Runs the Vue TypeScript compiler to catch type errors before runtime. The `--noEmit` flag means it only checks types without generating JavaScript files.

**`npm run lint`** - Runs ESLint and automatically fixes issues
```json
"lint": "eslint . --fix"
```

[ESLint](https://eslint.org/) analyzes your code for problems and enforces style guidelines. The `--fix` flag automatically corrects many issues.

#### Testing Scripts

**`npm run test`** - Runs Vitest unit tests
```json
"test": "vitest"
```

Executes your test suite using [Vitest](https://vitest.dev/), a fast testing framework powered by Vite.

**`npm run coverage`** - Generates a test coverage report
```json
"coverage": "vitest --coverage"
```

Creates a detailed report showing which parts of your code are tested.

### Running Scripts

To execute any script, use `npm run` followed by the script name:

```bash
# Development
npm run vite:dev

# Production build
npm run vite:build

# Quality checks
npm run typecheck
npm run lint

# Testing
npm run test
npm run coverage
```

Some scripts support additional arguments. For example, to run tests in watch mode:

```bash
npm run test -- --watch
```

The `--` separator tells NPM to pass everything after it directly to the underlying command.

### Creating Custom Scripts

You can add your own scripts for project-specific tasks. For example:

```json
{
  "scripts": {
    "dev": "npm run vite:dev",
    "build": "npm run typecheck && npm run vite:build",
    "deploy": "npm run build && rsync -av public/ user@server:/var/www/",
    "clean": "rm -rf public/assets node_modules"
  }
}
```

- **`dev`** - Shorter alias for starting development
- **`build`** - Type-check before building
- **`deploy`** - Build and upload to a server
- **`clean`** - Remove generated files

> [!TIP]
> Use `&&` to chain commands sequentially (the second command runs only if the first succeeds). For cross-platform compatibility in complex setups, consider tools like `npm-run-all`.

## Build Commands with Bakery

While you can run NPM commands directly, UserFrosting provides convenient [Bakery commands](/cli/commands) that wrap common frontend tasks.

### Building Assets

To run Vite through Bakery:

```bash
php bakery assets:vite
```

This runs the development workflow by default when `assets.vite.dev = true`. To force a production build:

```bash
php bakery assets:vite --production
```

The `assets:build` command is a higher-level alias used by `bake`. Depending on your environment, it installs dependencies and performs the production build when needed.

```bash
php bakery assets:build
```

### Dependency Commands with Bakery

Bakery also provides wrappers for dependency operations:

```bash
php bakery assets:install
php bakery assets:update
```

- `assets:install` maps to `npm install`
- `assets:update` maps to `npm update`

### NPM vs Bakery Commands

You can use either NPM commands or Bakery commands:

| Task                 | NPM Command          | Bakery Command                        |
|----------------------|----------------------|---------------------------------------|
| Install dependencies | `npm install`        | `php bakery assets:install`           |
| Update dependencies  | `npm update`         | `php bakery assets:update`            |
| Run dev server       | `npm run vite:dev`   | `php bakery assets:vite`              |
| Build for production | `npm run vite:build` | `php bakery assets:vite --production` |
