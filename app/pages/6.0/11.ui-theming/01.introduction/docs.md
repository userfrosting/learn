---
title: Project Structure
description: Learn how UserFrosting organizes frontend assets and how to navigate the project structure for efficient development.
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
