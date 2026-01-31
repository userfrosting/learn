---
title: Upgrading
description: Guide for upgrading to UserFrosting 6.0 from previous versions.
wip: true
---

#### Chapter 22

# Upgrading to UserFrosting 6.0

UserFrosting 6.0 represents a major evolution of the framework, consolidating what was previously spread across six separate repositories into a single, unified monorepo. This structural change, combined with a modernized frontend stack and improved developer experience, makes this one of the most significant updates in UserFrosting's history.

While the changes are substantial, the upgrade process is manageable when approached systematically. This chapter guides you through migrating from UserFrosting 5.1 to 6.0, explaining what's changed, why it changed, and how to update your application to take advantage of the new architecture.

## What's New in 6.0

UserFrosting 6.0 brings several major improvements:

- **Unified Monorepo**: All components (Framework, Core, Account, Admin, AdminLTE) consolidated into one repository
- **Modern Frontend Stack**: Transition from Webpack Encore to Vite with Hot Module Replacement
- **Vue 3 & TypeScript**: First-class support for modern JavaScript development
- **UIkit 3**: Updated UI framework replacing Bootstrap (in AdminLTE theme)
- **Improved Developer Experience**: Faster build times, better error messages, simpler configuration
- **PHP 8.1+ Required**: Leveraging modern PHP features and improved performance

## Should You Upgrade?

If you're starting a new project, absolutely use UserFrosting 6.0. For existing applications, consider:

- **Green light**: If you're actively developing and can allocate migration time
- **Yellow light**: If your app is in maintenance mode but could benefit from security updates
- **Red light**: If your app is stable and you don't need new features (5.1 will receive critical security updates)

UserFrosting 5.1 continues to receive security updates, so there's no immediate pressure to upgrade production applications. However, 6.0's improvements make it worthwhile for active projects.

## Before You Begin

> [!IMPORTANT]
> **Back up your application and database** before starting the upgrade process. Test the upgrade in a development environment first, never directly in production.

Requirements for UserFrosting 6.0:
- PHP 8.1 or higher (8.4 recommended)
- Composer 2
- Node.js 18.0 or higher (24 LTS recommended)
- npm 9.0 or higher

## Upgrade Strategy

The migration from 5.1 to 6.0 involves several layers:

1. **Repository structure**: Moving from multiple repositories to monorepo
2. **Dependencies**: Updating Composer and npm packages
3. **Frontend assets**: Migrating from Webpack Encore to Vite
4. **Configuration**: Adjusting for new structure and features
5. **Custom code**: Updating your sprinkles for API changes

This chapter walks you through each of these steps systematically.
