---
title: Introduction to UF6
description: Complete upgrade guide for migrating from UserFrosting 5.1 to 6.0
---

UserFrosting 6.0 represents a major evolution of the framework, replacing the aging Jquery, Handlebars and Bootstrap frontend stack with a modern Vite + Vue 3 + UIkit 3 architecture. 

This update also includes a complete restructuring of the codebase for developing UserFrosting itself, consolidating what was previously spread across six separate repositories into a single, unified monorepo. This structural change makes this the second most significant updates in UserFrosting's history, right after the initial release of 5.0 which saw a complete rewrite of the PHP codebase.

While the changes are substantial, the upgrade process is manageable when approached systematically. This chapter guides you through migrating from UserFrosting 5.1 to 6.0, explaining what's changed, why it changed, and how to update your application to take advantage of the new architecture.

## Quick Overview

UserFrosting 6.0 brings several major improvements:

- **Brand new UI**: A fresh, original and modern design with improved usability and accessibility
- **Modern Frontend Stack**: Transition from Webpack Encore to Vite with Hot Module Replacement
- **Vue 3 & TypeScript**: First-class support for modern JavaScript development
- **UIkit 3**: Updated UI framework replacing Bootstrap (in AdminLTE theme)
- **Improved Developer Experience**: Faster build times, better error messages, simpler configuration
- **PHP 8.1+ Required**: Leveraging modern PHP features and improved performance
- **Brand new Learn documentation**: Replaced Grav with a custom-built documentation site based on UserFrosting 6 (this site you're on right now!) for better performance and maintainability 
- **Unified Monorepo for developing UserFrosting**: All components (Framework, Core, Account, Admin, AdminLTE) consolidated into one repository

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

### Base Requirements
- **Minimum PHP version**: 8.1 (up from 8.0)
- **Recommended PHP version**: 8.4
- **Node.js requirement**: 18.0+ (LTS 24 recommended)

## Should You Upgrade?

If you're starting a new project, absolutely use UserFrosting 6.0. For existing applications, consider:

- **Green light**: If you're actively developing and can allocate migration time
- **Yellow light**: If your app is in maintenance mode but could benefit from security updates
- **Red light**: If your app is stable and you don't need new features (5.1 will receive critical security updates)

UserFrosting 5.1 might continue to receive security updates, so there's no immediate pressure to upgrade production applications. However, 6.0's improvements make it worthwhile for active projects.

> [!WARNING]
> This is a major version upgrade with breaking changes. Allocate appropriate time for testing and migration. UserFrosting 6 is not a drop-in replacement for UserFrosting 5. The whole frontend has been redesigned, with Vue instead of jQuery. This will require most sprinkle and app to be updated manually.

### Before You Begin

> [!IMPORTANT]
> **Back up your application and database** before starting the upgrade process. Test the upgrade in a development environment first, never directly in production.

### Upgrade Strategy

The migration from 5.1 to 6.0 involves several layers:

1. **Repository structure**: Moving from multiple repositories to monorepo
2. **Dependencies**: Updating Composer and npm packages
3. **Frontend assets**: Migrating from Webpack Encore to Vite
4. **Configuration**: Adjusting for new structure and features
5. **Custom code**: Updating your sprinkles for API changes

This chapter walks you through each of these steps systematically.
