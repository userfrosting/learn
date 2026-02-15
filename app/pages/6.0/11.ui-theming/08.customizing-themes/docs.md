---
title: Customizing Themes
description: Learn how to customize UIkit variables, extend Pink-Cupcake, and create your own themes in UserFrosting.
wip: true
---

UserFrosting's default **Pink-Cupcake** theme is built on UIkit and uses **LESS** for styling. This page teaches you how to customize the theme's appearance, override variables, and create your own branded experience.

## Understanding the Theme Structure

The Pink-Cupcake theme is structured as follows:

```
@userfrosting/theme-pink-cupcake/
├── less/
│   ├── main.less           # Main entry point
│   ├── variables.less       # UIkit variable overrides
│   ├── components/          # Custom component styles
│   └── mixins/              # Reusable LESS mixins
├── src/
│   ├── index.ts             # Vue plugin entry
│   └── components/          # Vue components
└── package.json
```

## Customizing Variables

The easiest way to customize your theme is by overriding LESS variables in your application's `theme.less` file.

**`app/assets/theme.less`:**
```less
// Import Pink Cupcake theme
@import '@userfrosting/theme-pink-cupcake/less/main.less';

// Override UIkit variables
@global-primary-background: #ff6b6b;        // Primary color
@global-secondary-background: #4ecdc4;      // Secondary color
@global-success-background: #51cf66;        // Success color
@global-warning-background: #ffd43b;        // Warning color
@global-danger-background: #ff6b6b;         // Danger color

// Typography
@global-font-family: 'Inter', sans-serif;
@global-font-size: 16px;
@global-line-height: 1.6;

// Spacing
@global-margin: 20px;
@global-gutter: 30px;

// Border radius
@global-border-radius: 8px;

// Custom styles
body {
    background: #f9fafb;
}
```

### Common Variables to Customize

#### Colors

```less
// Brand colors
@global-primary-background: #0066cc;
@global-secondary-background: #6c757d;

// State colors
@global-success-background: #28a745;
@global-warning-background: #ffc107;
@global-danger-background: #dc3545;
@global-muted-color: #6c757d;

// Link colors
@global-link-color: #0066cc;
@global-link-hover-color: #004080;
```

#### Typography

```less
@global-font-family: 'Roboto', sans-serif;
@global-font-size: 16px;
@global-line-height: 1.5;

@base-heading-font-family: 'Montserrat', sans-serif;
@base-h1-font-size: 2.5rem;
@base-h2-font-size: 2rem;
@base-h3-font-size: 1.75rem;
```

#### Spacing

```less
@global-margin: 20px;
@global-small-margin: 10px;
@global-medium-margin: 30px;
@global-large-margin: 50px;

@global-gutter: 30px;
@global-small-gutter: 15px;
```

#### Borders

```less
@global-border-radius: 6px;
@global-border-width: 1px;
@global-border: #e5e5e5;
```

#### Buttons

```less
@button-font-size: 14px;
@button-line-height: 38px;
@button-padding-horizontal: 30px;
@button-default-background: #f8f8f8;
@button-default-color: #333;
@button-primary-background: @global-primary-background;
```

## Custom Component Styles

Add your own component styles after importing the theme:

**`app/assets/theme.less`:**
```less
@import '@userfrosting/theme-pink-cupcake/less/main.less';

// Custom card styles
.custom-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: @global-medium-margin;
    border-radius: @global-border-radius;

    h3 {
        color: white;
        margin-top: 0;
    }
}

// Custom button variant
.uk-button-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;

    &:hover {
        background: linear-gradient(135deg, #5568d3 0%, #63408b 100%);
    }
}

// Custom table styling
.data-table {
    .uk-table {
        thead {
            background: @global-primary-background;
            color: white;

            th {
                color: white;
            }
        }

        tbody tr:hover {
            background: lighten(@global-primary-background, 45%);
        }
    }
}
```

## Extending Pink-Cupcake Components

Override specific Pink-Cupcake component styles:

```less
@import '@userfrosting/theme-pink-cupcake/less/main.less';

// Override sidebar width
@sidebar-width: 280px;

// Customize navigation
.uk-navbar {
    background: darken(@global-primary-background, 10%);
}

// Customize cards
.uk-card-default {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

    &:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
}

// Customize forms
.uk-form-label {
    font-weight: 600;
    color: #333;
}

.uk-input,
.uk-textarea,
.uk-select {
    border: 2px solid #e5e5e5;

    &:focus {
        border-color: @global-primary-background;
    }
}
```

## Using Custom Fonts

### From Google Fonts

**`app/assets/theme.less`:**
```less
// Import fonts
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

@import '@userfrosting/theme-pink-cupcake/less/main.less';

// Override font family
@global-font-family: 'Inter', sans-serif;
```

### Self-Hosted Fonts

Place font files in `app/assets/public/fonts/` and reference them:

```less
@font-face {
    font-family: 'CustomFont';
    src: url('/assets/fonts/customfont.woff2') format('woff2'),
         url('/assets/fonts/customfont.woff') format('woff');
    font-weight: 400;
    font-style: normal;
}

@import '@userfrosting/theme-pink-cupcake/less/main.less';

@global-font-family: 'CustomFont', sans-serif;
```

## Dark Mode

Create a dark mode by overriding color variables:

```less
@import '@userfrosting/theme-pink-cupcake/less/main.less';

// Dark mode styles
.dark-mode {
    background: #1a1a1a;
    color: #e5e5e5;

    // Override UIkit components for dark mode
    .uk-card-default {
        background: #2a2a2a;
        color: #e5e5e5;
    }

    .uk-button-default {
        background: #333;
        color: #e5e5e5;

        &:hover {
            background: #444;
        }
    }

    .uk-input,
    .uk-textarea,
    .uk-select {
        background: #2a2a2a;
        color: #e5e5e5;
        border-color: #444;
    }

    // Links
    a {
        color: lighten(@global-primary-background, 20%);

        &:hover {
            color: lighten(@global-primary-background, 30%);
        }
    }
}
```

**Toggle dark mode with JavaScript:**
```typescript
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode')
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'))
}

// Load preference on page load
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode')
}
```

## Creating a Custom Theme

For a completely custom theme, create your own theme package:

### 1. Create Theme Structure

```
my-custom-theme/
├── package.json
├── less/
│   ├── main.less
│   ├── variables.less
│   └── components/
├── src/
│   ├── index.ts
│   └── components/
└── vite.config.ts
```

### 2. Package Configuration

**`package.json`:**
```json
{
    "name": "@mycompany/theme-custom",
    "version": "1.0.0",
    "type": "module",
    "main": "dist/index.js",
    "files": ["dist", "less"],
    "dependencies": {
        "uikit": "^3.16.0",
        "vue": "^3.3.0"
    }
}
```

### 3. Theme Entry Point

**`less/main.less`:**
```less
// Import UIkit base
@import 'uikit/src/less/uikit.less';

// Your variables
@import 'variables.less';

// Your custom components
@import 'components/header.less';
@import 'components/sidebar.less';
@import 'components/cards.less';
```

### 4. Use Custom Theme

**`app/assets/main.ts`:**
```typescript
import { createApp } from 'vue'
import App from './App.vue'

// Import your custom theme instead of Pink-Cupcake
import CustomTheme from '@mycompany/theme-custom'

const app = createApp(App)
app.use(CustomTheme)

app.mount('#app')
```

**`app/assets/theme.less`:**
```less
// Import your custom theme
@import '@mycompany/theme-custom/less/main.less';

// Additional overrides
@global-primary-background: #ff6b6b;
```

## LESS Tips and Tricks

### Using Variables in Calculations

```less
@sidebar-width: 250px;
@navbar-height: 60px;

.main-content {
    margin-left: @sidebar-width;
    padding-top: @navbar-height;
    min-height: calc(100vh - @navbar-height);
}
```

### Mixins for Reusable Styles

```less
.button-variant(@bg, @color) {
    background: @bg;
    color: @color;

    &:hover {
        background: darken(@bg, 10%);
    }
}

.uk-button-custom {
    .button-variant(#667eea, white);
}
```

### Nested Selectors

```less
.uk-card {
    padding: @global-margin;

    .uk-card-title {
        margin-bottom: @global-small-margin;
    }

    &.uk-card-hover:hover {
        box-shadow: 0 14px 25px rgba(0,0,0,0.16);
    }
}
```

## Best Practices

1. **Override variables, don't replace**: Use theme variables for consistency
2. **Keep customizations in one file**: Maintain all overrides in `theme.less`
3. **Use semantic naming**: Name custom classes by purpose, not appearance
4. **Test responsive layouts**: Verify customizations work on all screen sizes
5. **Document your changes**: Comment why specific overrides were needed
6. **Consider dark mode**: Design with both light and dark themes in mind

## Debugging Styles

### Browser DevTools

Use browser DevTools to inspect elements and see which styles are applied:

1. Right-click element → "Inspect"
2. View computed styles
3. Find which LESS file defined the style
4. Override in your `theme.less`

### Source Maps

Vite generates source maps to help trace styles back to LESS files.

## Next Steps

- **[UIkit Variables](https://getuikit.com/docs/less)**: Complete list of UIkit LESS variables
- **[LESS Documentation](https://lesscss.org/)**: Learn more about LESS features
- **[Color Palette Generator](https://coolors.co/)**: Create color schemes for your theme

## Resources

- [UIkit Theming Guide](https://getuikit.com/docs/less)
- [LESS Language Features](https://lesscss.org/features/)
- [Web Accessibility](https://www.w3.org/WAI/WCAG21/quickref/): Ensure your theme is accessible
