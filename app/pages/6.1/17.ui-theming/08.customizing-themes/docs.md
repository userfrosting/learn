---
title: Customizing Themes
description: Override Pink-Cupcake and UIkit variables safely with LESS, using real import order and source files.
---

UserFrosting's visual theme is built with [LESS](https://lesscss.org/), a CSS preprocessor. Both Pink-Cupcake and UIkit define their visual style — colors, spacing, fonts, widths, border radii — as LESS variables. Customizing the look of your app is largely a matter of reassigning those variables in the right place.

The key rule is simple: **your project's `theme.less` file is the right place for all overrides.** You import the theme first, then your overrides follow.

## The Import Chain

Here's the full chain of imports, from your entry file down to UIkit:

1. `app/assets/main.ts` imports `./theme.less`.
2. `app/assets/theme.less` imports `@userfrosting/theme-pink-cupcake/less/main.less`.
3. Pink-Cupcake's `main.less` imports `uikit/src/less/uikit.theme.less`, then applies its own component overrides on top.

Any variable you define in `theme.less` — after the Pink-Cupcake import — will override the matching Pink-Cupcake or UIkit default before the final CSS is compiled.

## The Override Pattern

```less
@import '@userfrosting/theme-pink-cupcake/less/main.less';

/* Project-level variable overrides */
@sidebar-width: 300px;
@alert-primary-title-background: #6ab0de;
@breadcrumb-item-color: @global-link-color;
```

Keep your variable overrides at the top of the file, right below the import. Any project-specific custom selectors belong below those.

## Where to Find the Available Variables

You don't have to guess which variables exist — they're all defined in source files you can open right now:

| File | What's in it |
|------|-------------|
| `node_modules/@userfrosting/theme-pink-cupcake/src/less/components/variables.less` | Pink-Cupcake theme variables (sidebar, alerts, breadcrumbs, etc.) |
| `node_modules/@userfrosting/theme-pink-cupcake/src/less/main.less` | Import order and component hooks |
| `node_modules/uikit/src/less/variables-theme.less` | UIkit global design tokens (colors, spacing, fonts) |

Open these files whenever you want to find the exact variable name for something you want to change.

## Mini Recipes

### Override the main brand color

```less
@global-theme-color: #0f766e;
@global-secondary-background: #1f2937;
```

### Adjust the sidebar width

```less
@sidebar-width: 320px;
```

### Change the alert title background

```less
@alert-primary-title-background: #2563eb;
```

### Add a custom project class

Rather than overriding framework selectors directly, extend them with custom classes. This keeps your overrides predictable and avoids unexpected conflicts when the theme package updates:

```less
.my-highlight-card {
    border-left: 4px solid @global-theme-color;
    background: @global-primary-background-lighten;
    padding: @global-margin;
}
```
