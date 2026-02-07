---
title: 5.1.x to 6.0.x
description: Complete upgrade guide for migrating from UserFrosting 5.1 to 6.0
wip: true
---

# Upgrading from UserFrosting 5.1 to 6.0

Welcome to **UserFrosting 6.0**! This major release represents a significant evolution of the framework, bringing modern development tools, improved performance, and a streamlined architecture.

This guide walks you through the upgrade process step-by-step. While the changes are substantial, we've designed the migration path to be as smooth as possible.

> [!WARNING]
> This is a **major version upgrade** with breaking changes. Allocate appropriate time for testing and migration. Start with a development environment and thoroughly test before deploying to production.

> [!NOTE]
> Before proceeding, ensure you're running the latest version of UserFrosting 5.1 and have reviewed its changelog.

## What This Guide Covers

This section provides detailed information about:

- **[Changelog](/upgrading/51-to-60/changelog)**: Comprehensive list of changes, additions, and removals in 6.0
- **[Migration Guide](/upgrading/51-to-60/guide)**: Step-by-step instructions for upgrading your application
- **[What to Expect](/upgrading/51-to-60/what-to-expect)**: Future roadmap and what comes next after 6.0

## Quick Overview

The most significant changes in UserFrosting 6.0:

### Repository Consolidation
UserFrosting 5.1 was distributed across six separate repositories:
- Skeleton (main application)
- Framework (core framework)
- Core (core sprinkle)
- Account (user management sprinkle)
- Admin (admin panel sprinkle)
- AdminLTE (theme sprinkle)

UserFrosting 6.0 consolidates everything into a **single monorepo** at `https://github.com/userfrosting/monorepo`. This simplifies development, versioning, and dependency management.

### Frontend Modernization
**Before (5.1)**:
- Webpack Encore for asset bundling
- jQuery for DOM manipulation
- Bootstrap for UI components
- Handlebars for client-side templating

**After (6.0)**:
- Vite for lightning-fast asset bundling with HMR
- Vue 3 for reactive components
- UIkit 3 for modern UI framework (in AdminLTE theme)
- TypeScript for type-safe development
- Native ES modules

### Development Experience
- **Faster builds**: Vite's instant dev server startup vs Webpack's compilation
- **Hot Module Replacement**: See changes instantly without page refresh
- **Better errors**: Clear, actionable error messages
- **Simplified config**: Less boilerplate, more intuitive

### PHP Requirements
- **Minimum PHP version**: 8.1 (up from 8.0)
- **Recommended PHP version**: 8.4
- **Node.js requirement**: 18.0+ (LTS 24 recommended)

## Migration Time Estimate

Depending on your application's complexity:

- **Simple application** (minimal customization): 2-4 hours
- **Moderate application** (custom sprinkles, some frontend work): 1-2 days
- **Complex application** (extensive customizations, multiple sprinkles): 3-5 days

The frontend migration (Webpack → Vite) typically requires the most attention, especially if you have custom Vue components or complex asset pipelines.

## Getting Help

If you encounter issues during the upgrade:

1. Check the [Troubleshooting](/troubleshooting) chapter
2. Search existing [GitHub issues](https://github.com/userfrosting/monorepo/issues)
3. Ask in the [UserFrosting chat](https://chat.userfrosting.com)
4. Open a new issue with detailed information about your setup

Ready to begin? Start with the [Changelog](/upgrading/51-to-60/changelog) to understand what's changed, then follow the [Migration Guide](/upgrading/51-to-60/guide) for step-by-step instructions.
