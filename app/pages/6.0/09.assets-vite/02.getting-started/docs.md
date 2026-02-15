---
title: Getting Started
description: Learn how to configure Vite, understand the project structure, and set up your development environment in UserFrosting.
---

Let's get you set up with Vite! This guide will walk you through the configuration, explain how assets are organized, and help you establish a smooth development workflow. Don't worry if some concepts are new—we'll explain everything as we go.

## Project Structure

UserFrosting organizes assets in a standard structure:

```
your-project/
├── app/
│   └── assets/                 # Source assets
│       ├── main.ts             # Main entry point
│       ├── App.vue             # Root Vue component
│       ├── theme.less          # Custom styles
│       ├── router/             # Vue Router configuration
│       ├── components/         # Vue components
│       └── public/             # Static assets (copied as-is)
├── public/
│   └── assets/                 # Compiled output (generated)
│       ├── main-[hash].js
│       ├── main-[hash].css
│       └── .vite/
│           └── manifest.json   # Build manifest
├── vite.config.ts              # Vite configuration
└── package.json                # npm dependencies
```

## Understanding Package.json

The `package.json` file is the heart of any Node.js project, including UserFrosting's frontend asset management. Think of it as the instruction manual and blueprint for your project—it tells NPM (Node Package Manager) what your project needs, how to build it, and how to run various tasks.

> [!TIP]
> The `package.json` file is to JavaScript projects what `composer.json` is to PHP projects—it manages external dependencies and defines how to work with your project.

### What is Package.json?

According to the [official NPM documentation](https://docs.npmjs.com/cli/v10/configuring-npm/package-json), `package.json` is a JSON file that serves multiple critical purposes:

1. **Dependency Management** - Lists all external packages (libraries) your project needs
2. **Project Metadata** - Defines your project's name, version, description, and author
3. **Script Automation** - Contains commands for building, testing, and running your application
4. **Version Control** - Specifies exact or acceptable versions of each dependency
5. **Configuration** - Stores settings for various tools and build processes

### Package.json and NPM

[NPM (Node Package Manager)](https://www.npmjs.com/) is the default package manager for Node.js and the world's largest software registry. When you install NPM packages, the registry at [npmjs.com](https://www.npmjs.com/) hosts millions of open-source packages that developers can use in their projects.

Here's how `package.json` and NPM work together:

1. **Installation** - When you run `npm install`, NPM reads `package.json` to determine which packages to download
2. **Version Resolution** - NPM checks the version constraints (like `^3.4.0`) and installs compatible versions
3. **Dependency Tree** - NPM automatically installs each package's dependencies, creating a `node_modules` folder
4. **Lock File** - NPM generates `package-lock.json` to ensure everyone installs identical versions

> [!NOTE]
> The `package-lock.json` file is automatically generated and should be committed to version control. It locks exact versions of all dependencies, ensuring consistent installations across different environments. [Learn more about package-lock.json](https://docs.npmjs.com/cli/v10/configuring-npm/package-lock-json).

### UserFrosting's Package.json

UserFrosting's [Skeleton template](https://github.com/userfrosting/UserFrosting) includes a pre-configured `package.json` with everything you need to get started. You don't need to create this file from scratch—it's already set up with sensible defaults for Vite, Vue 3, TypeScript, and all necessary build tools.

**However, you can and should customize it to fit your project's needs:**

- **Add new dependencies** for features you need (e.g., date pickers, charts, UI components)
- **Remove unused dependencies** to reduce bundle size
- **Update versions** to get bug fixes and new features
- **Add custom scripts** for your specific workflows
- **Modify existing scripts** to change build behavior

For example, if your project needs a date picker library, you would add it:

```bash
npm install --save flatpickr
```

This automatically updates your `package.json` with the new dependency:

```json
{
  "dependencies": {
    "flatpickr": "^4.6.13"
  }
}
```

### Package.json Structure

Your `package.json` includes several key sections. Let's break down a typical UserFrosting configuration:

```json
{
  "name": "userfrosting-app",
  "version": "1.0.0",
  "type": "module",
  "scripts": {
    "vite:dev": "vite",
    "vite:build": "vite build",
    "typecheck": "vue-tsc --noEmit",
    "lint": "eslint . --fix",
    "test": "vitest",
    "coverage": "vitest --coverage"
  },
  "dependencies": {
    "vue": "^3.4.0",
    "vue-router": "^4.3.0",
    "axios": "^1.6.0",
    "pinia": "^2.1.0",
    "pinia-plugin-persistedstate": "^3.2.0",
    "uikit": "^3.21.0"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^5.1.0",
    "@modyfi/vite-plugin-yaml": "^1.1.0",
    "vite": "^5.4.0",
    "vite-plugin-vue-devtools": "^7.3.0",
    "typescript": "^5.5.0",
    "vue-tsc": "^2.0.0",
    "@types/node": "^20.14.0",
    "vitest": "^2.0.0",
    "eslint": "^9.6.0"
  }
}
```

#### Project Metadata

- **`name`** - Your project's identifier (used for publishing to NPM if public)
- **`version`** - Current version following [SemVer](https://semver.org/) format (MAJOR.MINOR.PATCH)
- **`type`** - Set to `"module"` to enable ES modules (import/export syntax)

#### Dependencies vs DevDependencies

Understanding the difference between these two sections is important:

**`dependencies`** - Packages required for your application to run in production. These are installed in all environments and included in your final build:
- **`vue`** - The Vue 3 framework for building reactive user interfaces
- **`vue-router`** - Official routing library for Vue.js single-page applications
- **`axios`** - Promise-based HTTP client for making API requests
- **`pinia`** - Official state management library for Vue 3 (replaces Vuex)
- **`pinia-plugin-persistedstate`** - Persists Pinia state to localStorage
- **`uikit`** - Frontend CSS framework for styling components

**`devDependencies`** - Packages only needed during development and building. These are not included in production bundles:
- **`@vitejs/plugin-vue`** - Vite plugin that compiles Vue Single File Components
- **`@modyfi/vite-plugin-yaml`** - Allows importing YAML files as JavaScript modules
- **`vite`** - The build tool itself
- **`vite-plugin-vue-devtools`** - Integrates Vue DevTools for debugging
- **`typescript`** - TypeScript compiler for type-safe JavaScript
- **`vue-tsc`** - TypeScript type-checker specifically for Vue components
- **`@types/node`** - TypeScript type definitions for Node.js APIs
- **`vitest`** - Fast unit testing framework powered by Vite
- **`eslint`** - JavaScript/TypeScript linter for code quality

> [!TIP]
> When installing new packages, use `npm install --save` (for dependencies) or `npm install --save-dev` (for devDependencies). The `--save` flag is now default in modern NPM versions, so `npm install package-name` adds to dependencies.

## NPM Scripts: Your Command Toolbox

The `scripts` section in `package.json` is one of its most powerful features. Scripts are custom commands that automate common tasks—think of them as shortcuts that save you from typing long, complex commands.

NPM scripts are shell commands defined in your `package.json` that you can run using `npm run <script-name>`. They provide a standardized way to execute tasks across different projects and teams. According to the [NPM scripts documentation](https://docs.npmjs.com/cli/v10/using-npm/scripts), scripts can:

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
- **Maintainable** - Update the command once, affects everyone

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
- **Source maps** - Makes debugging easier by mapping compiled code to source
- **Development server** - Runs on `http://localhost:5173` (by default)

When running, your browser loads assets directly from Vite's dev server for the fastest possible feedback loop.

**`npm run vite:build`** - Creates an optimized production build
```json
"vite:build": "vite build"
```

This generates production-ready assets with:
- **Code minification** - Removes whitespace and shortens variable names
- **Tree shaking** - Eliminates unused code from bundles
- **Asset optimization** - Compresses images and files
- **Code splitting** - Breaks code into smaller chunks for faster loading
- **Cache busting** - Adds hashes to filenames (e.g., `main-a1b2c3d4.js`)

Output goes to `public/assets/` and includes a manifest file for UserFrosting to reference assets.

> [!TIP]
> Both command can also be run from Bakery for a more integrated experience. See bellow for details.

#### Quality Assurance Scripts

**`npm run typecheck`** - Type-checks TypeScript without compilation
```json
"typecheck": "vue-tsc --noEmit"
```

Runs the Vue TypeScript compiler to catch type errors before runtime. The `--noEmit` flag means it only checks types without generating JavaScript files (Vite handles compilation). This helps catch bugs like:
- Calling functions with wrong argument types
- Accessing undefined properties
- Mismatched component props

**`npm run lint`** - Runs ESLint and automatically fixes issues
```json
"lint": "eslint . --fix"
```

[ESLint](https://eslint.org/) analyzes your code for problems and enforces style guidelines. The `--fix` flag automatically corrects issues like:
- Inconsistent indentation and spacing
- Missing semicolons or trailing commas
- Unused variables
- Code complexity warnings

This keeps your codebase clean and consistent across the team.

#### Testing Scripts

**`npm run test`** - Runs Vitest unit tests
```json
"test": "vitest"
```

Executes your test suite using [Vitest](https://vitest.dev/), a blazing-fast testing framework. Tests ensure your components and functions work correctly and help prevent regressions when making changes.

**`npm run coverage`** - Generates test coverage report
```json
"coverage": "vitest --coverage"
```

Creates a detailed report showing which parts of your code are tested. Coverage reports help identify untested code paths and improve test quality.

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

You can add your own scripts for project-specific tasks. For example, you might add:

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
- **`build`** - Type-check before building (catches errors early)
- **`deploy`** - Build and upload to server (automate deployment)
- **`clean`** - Remove generated files (useful for troubleshooting)

> [!TIP]
> Use `&&` to chain commands sequentially (second command runs only if first succeeds) or `&` to run commands in parallel. For cross-platform compatibility, consider using tools like `npm-run-all` for complex script orchestration.

## Managing Dependencies with Bakery

While you can run NPM commands directly, UserFrosting provides convenient [Bakery commands](/cli/commands) that wrap NPM functionality and integrate with your UserFrosting workflow. Think of these as UserFrosting's way of helping you manage frontend dependencies without leaving the PHP environment.

### Installing Dependencies

To install all dependencies listed in your `package.json`:

```bash
php bakery assets:install
```

**What it does:**
- Runs `npm install` to install dependencies
- Installs exact versions from `package-lock.json`
- Validates Node.js and NPM versions
- Ensures your `node_modules` folder is up to date

**When to use:**
- After cloning the repository
- After pulling changes that modify `package.json`
- When `node_modules/` is missing or corrupted
- As part of your deployment process

This is equivalent to running `npm install` directly, but provides additional validation and integrates with UserFrosting's environment checks.

### Updating Dependencies

To update dependencies to their latest compatible versions:

```bash
php bakery assets:update
```

**What it does:**
- Runs `npm update` to update packages
- Updates packages within the semver ranges specified in `package.json`
- Updates `package-lock.json` with new versions
- Preserves your specified version constraints

For example, if your `package.json` specifies `"vue": "^3.4.0"`, running `assets:update` might update to `3.5.2` (a newer minor version), but won't update to `4.0.0` (a new major version).

**When to use:**
- Regularly to get bug fixes and security patches
- After modifying version ranges in `package.json`
- Before starting work on a feature to ensure latest compatible versions

> [!WARNING]
> Running `assets:update` may introduce breaking changes if packages don't follow semantic versioning strictly. Always test thoroughly after updating dependencies, especially before deploying to production.

### Building Assets

To build your assets:

```bash
php bakery assets:vite
```

This runs the Vite development server for local development or run the production build based on your environnement configuration. By default, it runs in development mode (`assets.vite.dev = true`), but you can force production builds with the `--production` flag. 

```bash
php bakery assets:vite --production
```

The `assets:build` command is an alias for building assets with Vite or Webpack (depending on your configuration). In development mode, it runs `assets:install`. In production mode, it also builds the assets for deployment. This command in part of the `bake` command.

```bash
php bakery assets:build
```

### NPM vs Bakery Commands

You can use either NPM commands or Bakery commands—they accomplish the same goals:

| Task                 | NPM Command          | Bakery Command                        |
|----------------------|----------------------|---------------------------------------|
| Install dependencies | `npm install`        | `php bakery assets:install`           |
| Update dependencies  | `npm update`         | `php bakery assets:update`            |
| Run dev server       | `npm run vite:dev`   | `php bakery assets:vite`              |
| Build for production | `npm run vite:build` | `php bakery assets:vite --production` |

**When to use Bakery commands:**
- You prefer working in the PHP environment
- You're already using Bakery for other tasks
- You want integrated validation and error messages
- You're automating with PHP scripts

**When to use NPM directly:**
- You're more comfortable with Node.js tools
- You need to pass complex arguments
- You're using package-specific features
- You're working primarily on frontend code

## Next Steps

Now that you understand package.json, NPM scripts, and dependency management, learn about [Vite Configuration](/assets-vite/vite-configuration) to customize how assets are built and served.
