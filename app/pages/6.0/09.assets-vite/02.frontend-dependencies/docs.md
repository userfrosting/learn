---
title: Frontend Dependencies
description: Learn how package.json works and how to manage frontend dependencies in UserFrosting.
---

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
    ...
  },
  "dependencies": {
    ...
  },
  "devDependencies": {
    ...
  }
}
```

#### Project Metadata

- **`name`** - Your project's identifier (used for publishing to NPM if public)
- **`version`** - Current version following [SemVer](https://semver.org/) format (MAJOR.MINOR.PATCH)
- **`type`** - Set to `"module"` to enable ES modules (import/export syntax)

#### Dependencies vs DevDependencies

Understanding the difference between these two sections is important:

**`dependencies`** - Packages required for your application to run in production. In other words, they are necessary to run the code in the browser. These are installed in all environments and included in your final build. Examples of dependencies include:
- **`vue`** - The Vue.js framework for building user interfaces
- **`vue-router`** - Official router for Vue.js applications
- **`axios`** - Promise-based HTTP client for making API requests

**`devDependencies`** - Packages only needed during development and building. These are not included in production bundles. In other words, they are tools you use to build your assets, used in the command line or in your code IDE. Examples of devDependencies includes:
- **`vite`** - The build tool itself
- **`typescript`** - TypeScript compiler for type-safe JavaScript
- **`vitest`** - Fast unit testing framework powered by Vite
- **`eslint`** - JavaScript/TypeScript linter for code quality

> [!TIP]
> When installing new packages, use `npm install --save` (for dependencies) or `npm install --save-dev` (for devDependencies). The `--save` flag is now default in modern NPM versions, so `npm install package-name` adds to dependencies.
