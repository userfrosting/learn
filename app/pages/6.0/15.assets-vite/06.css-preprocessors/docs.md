---
title: CSS Preprocessors
description: Learn how to use CSS preprocessors like LESS and Sass with Vite for more powerful and maintainable stylesheets in your UserFrosting projects.
---

Vite supports LESS, Sass, and Stylus out of the box. Just import preprocessor files and install the appropriate package.

### LESS

UserFrosting uses LESS by default, particularly for theming. You can import any `.less` file directly in your JavaScript or Vue components, and Vite will handle the compilation.

**`app/assets/theme.less`:**
```less
// Import dependencies from `node_modules`
@import 'uikit/src/less/uikit.less';

// 1) Override UIkit variables before custom styles
@global-primary-background: #0066cc;
@global-secondary-background: #0f172a;
@global-success-background: #0f766e;
@global-font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
@global-border-radius: 10px;

// 2) Define project-specific variables
@my-margin: 20px;
@card-shadow: 0 10px 25px rgba(2, 6, 23, 0.08);

// 3) Create reusable mixins
.focus-ring(@color) {
    outline: 2px solid fade(@color, 35%);
    outline-offset: 2px;
}

.card-surface() {
    background: #fff;
    border-radius: @global-border-radius;
    box-shadow: @card-shadow;
}

// 4) Use nesting, variables, and mixins in components
.custom-container {
    padding: @my-margin;
    .card-surface();

    h1 {
        color: @global-primary-background;
        margin-bottom: 12px;
    }

    .button-primary {
        background-color: @global-primary-background;
        color: #fff;
        border: 0;
        border-radius: @global-border-radius;
        padding: 0.55rem 1rem;
        transition: background-color 0.2s ease;

        &:hover {
            background-color: darken(@global-primary-background, 7%);
        }

        &:focus-visible {
            .focus-ring(@global-primary-background);
        }
    }
}
```

Then import it in your entry file (for example `app/assets/main.ts`):

```ts
import './theme.less'
```

The less files will be compiled as plain CSS and included in your bundle, allowing you to use the defined styles and variables throughout your project.

### Sass/SCSS

If you prefer Sass, you'll need to install the `sass` package first. 

```bash
npm install -D sass
```

You can use either the indented syntax (`.sass`) or the SCSS syntax (`.scss`)

**`app/assets/styles.scss`:**
```scss
// 1) Theme tokens
$primary-color: #0066cc;
$success-color: #0f766e;
$radius: 10px;
$shadow: 0 10px 25px rgba(2, 6, 23, 0.08);

// 2) Reusable mixins
@mixin focus-ring($color) {
    outline: 2px solid rgba($color, 0.35);
    outline-offset: 2px;
}

@mixin button-variant($bg) {
    background: $bg;
    color: #fff;
    border: 0;
    border-radius: $radius;
    padding: 0.55rem 1rem;
    transition: background-color 0.2s ease;

    &:hover {
        background: darken($bg, 8%);
    }

    &:focus-visible {
        @include focus-ring($bg);
    }
}

// 3) Component styles
.panel {
    border-radius: $radius;
    box-shadow: $shadow;
    padding: 1rem;

    h2 {
        margin-top: 0;
        color: $primary-color;
    }
}

.button {
    @include button-variant($primary-color);

    &.button-success {
        @include button-variant($success-color);
    }
}
```

Import it in your entry file:

```ts
import './styles.scss'
```

> [!WARNING]
> UserFrosting uses **Less** as its default CSS preprocessor for the default theme. If you choose to use SASS/SCSS instead, you will not be able to easily customize or extend the default theme's styles, as the theme's variables and mixins are written in Less. Consider this trade-off when choosing your preprocessor.
