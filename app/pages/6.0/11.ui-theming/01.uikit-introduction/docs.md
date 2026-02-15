---
title: UIkit Introduction
description: Learn the fundamentals of UIkit, UserFrosting's CSS framework for building beautiful user interfaces.
wip: true
---

[UIkit](https://getuikit.com/) is a lightweight, modular front-end framework that provides the CSS foundation for UserFrosting's default theme. This page introduces you to UIkit's core components and how to use them in your UserFrosting application.

## What is UIkit?

UIkit is a comprehensive CSS framework that provides:

- **Pre-built components**: Buttons, forms, cards, modals, and more
- **Responsive grid system**: Flexible layouts that adapt to any screen size
- **JavaScript components**: Interactive elements like modals, dropdowns, and tooltips
- **Utility classes**: Spacing, alignment, visibility, and other helpers
- **LESS-based**: Easy customization through variables

Unlike heavier frameworks like Bootstrap, UIkit maintains a smaller footprint while still providing all the components you need for modern web applications.

## Core Components

### Buttons

UIkit provides several button styles:

```html
<button class="uk-button uk-button-default">Default</button>
<button class="uk-button uk-button-primary">Primary</button>
<button class="uk-button uk-button-secondary">Secondary</button>
<button class="uk-button uk-button-danger">Danger</button>
<button class="uk-button uk-button-text">Text</button>
```

**Button sizes**:
```html
<button class="uk-button uk-button-primary uk-button-small">Small</button>
<button class="uk-button uk-button-primary">Default</button>
<button class="uk-button uk-button-primary uk-button-large">Large</button>
```

### Grid System

UIkit uses a flexible grid system based on flexbox:

```html
<div class="uk-grid" uk-grid>
    <div class="uk-width-1-2">Half width</div>
    <div class="uk-width-1-2">Half width</div>
</div>

<div class="uk-grid" uk-grid>
    <div class="uk-width-1-3">One third</div>
    <div class="uk-width-2-3">Two thirds</div>
</div>

<div class="uk-grid" uk-grid>
    <div class="uk-width-1-4">Quarter</div>
    <div class="uk-width-3-4">Three quarters</div>
</div>
```

**Responsive widths**:
```html
<div class="uk-grid" uk-grid>
    <div class="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m">
        <!-- Full width on mobile, half on small screens, third on medium+ -->
    </div>
</div>
```

### Cards

Cards are versatile containers for content:

```html
<div class="uk-card uk-card-default uk-card-body">
    <h3 class="uk-card-title">Card Title</h3>
    <p>Card content goes here.</p>
</div>

<div class="uk-card uk-card-primary uk-card-body">
    <h3 class="uk-card-title">Primary Card</h3>
    <p>Primary styled card.</p>
</div>
```

**Card with header and footer**:
```html
<div class="uk-card uk-card-default">
    <div class="uk-card-header">
        <h3 class="uk-card-title">Header</h3>
    </div>
    <div class="uk-card-body">
        <p>Content</p>
    </div>
    <div class="uk-card-footer">
        <button class="uk-button uk-button-primary">Action</button>
    </div>
</div>
```

### Modals

UIkit modals are simple to create:

```html
<!-- Modal toggle button -->
<button class="uk-button uk-button-primary" uk-toggle="target: #my-modal">
    Open Modal
</button>

<!-- Modal -->
<div id="my-modal" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="uk-modal-title">Modal Title</h2>
        <p>Modal content goes here.</p>
        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-default uk-modal-close">Cancel</button>
            <button class="uk-button uk-button-primary">Save</button>
        </div>
    </div>
</div>
```

**Programmatic control**:
```typescript
import UIkit from 'uikit'

// Show modal
UIkit.modal('#my-modal').show()

// Hide modal
UIkit.modal('#my-modal').hide()
```

### Notifications

Display temporary messages to users:

```typescript
import UIkit from 'uikit'

// Basic notification
UIkit.notification('Hello world!')

// With options
UIkit.notification('User saved successfully!', {
    status: 'success',
    pos: 'top-right',
    timeout: 5000
})

// Status types
UIkit.notification('Info message', 'primary')
UIkit.notification('Success message', 'success')
UIkit.notification('Warning message', 'warning')
UIkit.notification('Error message', 'danger')
```

### Forms

UIkit provides consistent form styling:

```html
<form class="uk-form-stacked">
    <div class="uk-margin">
        <label class="uk-form-label" for="username">Username</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="username" type="text" placeholder="Enter username">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="email">Email</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="email" type="email" placeholder="Enter email">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="message">Message</label>
        <div class="uk-form-controls">
            <textarea class="uk-textarea" id="message" rows="5"></textarea>
        </div>
    </div>

    <div class="uk-margin">
        <button class="uk-button uk-button-primary" type="submit">Submit</button>
    </div>
</form>
```

**Form validation states**:
```html
<input class="uk-input uk-form-success" type="text" value="Valid input">
<input class="uk-input uk-form-danger" type="text" value="Invalid input">
```

### Icons

UIkit includes an icon library:

```html
<!-- Using uk-icon attribute -->
<span uk-icon="user"></span>
<span uk-icon="icon: heart; ratio: 2"></span>

<!-- As buttons -->
<button class="uk-button uk-button-default">
    <span uk-icon="icon: plus"></span> Add Item
</button>
```

## Utility Classes

UIkit provides many utility classes for common tasks:

### Margin and Padding

```html
<div class="uk-margin">Default margin</div>
<div class="uk-margin-small">Small margin</div>
<div class="uk-margin-large">Large margin</div>
<div class="uk-padding">Padding</div>
```

### Text Alignment

```html
<p class="uk-text-left">Left aligned</p>
<p class="uk-text-center">Center aligned</p>
<p class="uk-text-right">Right aligned</p>
```

### Visibility

```html
<!-- Responsive visibility -->
<div class="uk-visible@s">Visible on small screens and up</div>
<div class="uk-hidden@m">Hidden on medium screens and up</div>
```

## Using UIkit with Vue

In UserFrosting, you'll often combine UIkit styling with Vue reactivity:

```vue
<template>
    <div class="uk-card uk-card-default uk-card-body">
        <h3 class="uk-card-title">{{ title }}</h3>
        <p>{{ content }}</p>
        <button 
            class="uk-button uk-button-primary" 
            @click="handleClick"
        >
            {{ buttonText }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const title = ref('Dynamic Card')
const content = ref('This card combines UIkit styling with Vue reactivity!')
const buttonText = ref('Click Me')

function handleClick() {
    buttonText.value = 'Clicked!'
}
</script>
```

## Next Steps

Now that you understand UIkit basics, explore:

- **[Forms](/ui-theming/forms)**: Build validated forms with UIkit styling
- **[Tables](/ui-theming/tables)**: Create data tables with UIkit components
- **[Customizing Themes](/ui-theming/customizing-themes)**: Customize UIkit variables for your brand

## Resources

- [UIkit Documentation](https://getuikit.com/docs/introduction)
- [UIkit Components](https://getuikit.com/docs/grid)
- [UIkit Icons](https://getuikit.com/docs/icon)
- [UIkit GitHub](https://github.com/uikit/uikit)
