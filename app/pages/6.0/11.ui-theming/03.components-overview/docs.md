---
title: Common UI Components
description: Build app layout and UI with verified Pink-Cupcake Vue components and UIkit classes.
---

Pink-Cupcake gives you a set of pre-built Vue components, styled with UIkit classes, that handle the most common app interface patterns. For example, instead of building a navbar from scratch, you can use `UFNavBar`. Instead of writing your own paginated data table, you can use the Sprunje component set.

One common point of confusion is the difference between Pink-Cupcake components and UIkit classes. They're both part of your UI, but they play different roles:

- **Pink-Cupcake components** are Vue components you use as tags in your templates — `<UFNavBar />`, `<UFSideBarItem />`, `<UFAlert />`. They are styled with UIkit but also include built-in structure, behavior, and API for common UI patterns.
- **UIkit classes** are CSS utility classes you apply directly to HTML elements — `class="uk-card uk-grid-small uk-margin"`.

## Pink-Cupcake Components

These are the components exported and globally registered by the installed theme package. They're organized into three groups: layout and navigation, feedback, and data tables.

### Layout and Navigation

#### `UFNavBar`

The main navigation bar at the top of the page. Sticks to the top on scroll, shows a logo/title link on the left, and navigation items on the right. On mobile, it renders a hamburger button that toggles the sidebar panel.

**Props:**
| Prop    | Type               | Default | Description                    |
|---------|--------------------|---------|--------------------------------|
| `title` | `string`           | `''`    | Brand text or logo label       |
| `to`    | `string \| object` | `''`    | Route destination for the logo |

**Slots:**
| Slot      | Description                                                                                        |
|-----------|----------------------------------------------------------------------------------------------------|
| `title`   | Overrides the `title` prop with custom content. Use it to display a logo or other custom elements. |
| `default` | Navigation items displayed on the right (desktop)                                                  |

```vue
<UFNavBar title="My App" :to="{ name: 'home' }">
    <UFNavBarItem :to="{ name: 'about' }" label="About" />
</UFNavBar>
```

#### `UFNavBarItem`

A single link inside `UFNavBar`. Handles both internal router links and external URLs. Automatically adds an active class when the current route matches.

**Props:**
| Prop    | Type               | Default | Description                  |
|---------|--------------------|---------|------------------------------|
| `label` | `string`           | `''`    | Link text                    |
| `to`    | `string \| object` | `''`    | Route object or external URL |

**Slots:**
| Slot      | Description                |
|-----------|----------------------------|
| `default` | Overrides the `label` prop |

```vue
<UFNavBarItem :to="{ name: 'dashboard' }" label="Dashboard" />
<UFNavBarItem to="https://userfrosting.com" label="UserFrosting" />
```

#### `UFNavBarDropdown`

A navbar item with a dropdown panel. Useful for grouping sub-links or embedding content like a login form directly in the navbar.

**Props:**
| Prop    | Type     | Default | Description         |
|---------|----------|---------|---------------------|
| `label` | `string` | `''`    | Trigger button text |

**Slots:**
| Slot      | Description                                         |
|-----------|-----------------------------------------------------|
| `label`   | Overrides the trigger label                         |
| `default` | Dropdown content, typically `UFNavBarItem` elements |

```vue
<UFNavBarDropdown label="Account">
    <UFNavBarItem :to="{ name: 'account.settings' }" label="Settings" />
</UFNavBarDropdown>
```

#### `UFSideBar`

The sidebar navigation container. On desktop it renders as a fixed-position panel on the side. On mobile it becomes an off-canvas drawer toggled by the `UFNavBar` hamburger button. No props needed — just fill it with items.

**Slots:**
| Slot      | Description                                |
|-----------|--------------------------------------------|
| `default` | Sidebar content (items, dropdowns, labels) |

```vue
<UFSideBar>
    <UFSideBarItem :to="{ name: 'dashboard' }" label="Dashboard" icon="home" />
</UFSideBar>
```

#### `UFSideBarItem`

A sidebar navigation link. Supports UIkit icons and FontAwesome icons side by side. Highlights automatically when its route is active, and closes the mobile off-canvas panel on click.

**Props:**
| Prop     | Type               | Default | Description                                 |
|----------|--------------------|---------|---------------------------------------------|
| `label`  | `string`           | `''`    | Item text                                   |
| `to`     | `string \| object` | `''`    | Route object or external URL                |
| `icon`   | `string`           | `''`    | UIkit icon name (e.g. `'home'`)             |
| `faIcon` | `string`           | `''`    | FontAwesome icon name (e.g. `'gauge-high'`) |

**Slots:**
| Slot      | Description                |
|-----------|----------------------------|
| `default` | Overrides the `label` prop |

```vue
<UFSideBarItem :to="{ name: 'admin.users' }" label="Users" faIcon="users" />
<UFSideBarItem :to="{ name: 'reports' }" label="Reports" icon="album" />
```

#### `UFSideBarDropdown`

A collapsible section inside the sidebar. Expands automatically when any child route is active. Use it to group related pages under a single heading.

**Props:**
| Prop        | Type               | Default | Description                                      |
|-------------|--------------------|---------|--------------------------------------------------|
| `label`     | `string`           | `''`    | Section title                                    |
| `to`        | `string \| object` | `''`    | Route used to determine if the group should open |
| `icon`      | `string`           | `''`    | UIkit icon name                                  |
| `faIcon`    | `string`           | `''`    | FontAwesome icon name                            |
| `hideCaret` | `boolean`          | `false` | Hide the expand/collapse triangle                |

**Slots:**
| Slot      | Description                                    |
|-----------|------------------------------------------------|
| `label`   | Overrides the label text                       |
| `default` | Sub-items (typically `UFSideBarItem` elements) |

```vue
<UFSideBarDropdown label="Reports" faIcon="chart-bar">
    <UFSideBarItem :to="{ name: 'reports.daily' }" label="Daily" />
    <UFSideBarItem :to="{ name: 'reports.monthly' }" label="Monthly" />
</UFSideBarDropdown>
```

#### `UFSideBarLabel`

A non-clickable section heading used to visually separate groups of items in the sidebar.

**Props:**
| Prop    | Type     | Required | Description         |
|---------|----------|----------|---------------------|
| `label` | `string` | ✅        | The text to display |

```vue
<UFSideBarLabel label="Administration" />
<UFSideBarItem :to="{ name: 'admin.users' }" label="Users" />
```

#### `UFHeaderPage`

Renders the current page title, description, and breadcrumb trail at the top of the content area. It reads everything automatically from the route's `meta` fields via `usePageMeta()` — no props needed. If the route has no title or meaningful breadcrumbs, the component hides itself entirely.

```vue
<main class="uk-container">
    <UFHeaderPage />
    <RouterView />
</main>
```

> [!TIP]
> Set `title` and `description` in your route's `meta` field to populate this component automatically.

### Feedback

#### `UFAlert`

Displays an inline alert message from an `AlertInterface` object. Typically used to surface API errors or success messages next to a form. Supports multiple severity styles and an optional close button.

**Props:**
| Prop    | Type             | Required | Description       |
|---------|------------------|----------|-------------------|
| `alert` | `AlertInterface` | ✅        | Alert data object |

The `AlertInterface` has the following fields:

| Field         | Type       | Description                                                                                                 |
|---------------|------------|-------------------------------------------------------------------------------------------------------------|
| `title`       | `string`   | Alert heading                                                                                               |
| `description` | `string`   | Body text (rendered as HTML)                                                                                |
| `style`       | `Severity` | An instance of `Severity` — e.g. `Severity.Success`, `Severity.Warning`, `Severity.Danger`, `Severity.Info` |
| `closeBtn`    | `boolean`  | Show a dismiss button                                                                                       |
| `hideIcon`    | `boolean`  | Hide the automatic severity icon                                                                            |

**Slots:**
| Slot      | Description                         |
|-----------|-------------------------------------|
| `default` | Overrides the `description` content |

**Emits:**
| Emit    | Description                              |
|---------|------------------------------------------|
| `close` | Emitted when the close button is clicked |

```vue
<!-- Show an API error -->
<UFAlert v-if="apiError" :alert="apiError" @close="apiError = null" />

<!-- Hardcoded severity example -->
<UFAlert :alert="{ title: 'Password updated', style: Severity.Success, closeBtn: true }" />
```

#### `UFModal`

The base modal component. Wraps UIkit's modal system. Can be opened with a `uk-toggle` attribute or programmatically via `UIkit.modal('#id').show()`. Use this when the pre-built modal variants don't fit your needs.

**Props:**
| Prop       | Type      | Default | Description                    |
|------------|-----------|---------|--------------------------------|
| `closable` | `boolean` | `false` | Show an × close button         |
| `escClose` | `boolean` | `true`  | Close with the ESC key         |
| `bgClose`  | `boolean` | `true`  | Close by clicking the backdrop |

**Slots:**
| Slot      | Description  |
|-----------|--------------|
| `header`  | Modal header |
| `default` | Modal body   |
| `footer`  | Modal footer |

```vue
<a href="#my-modal" uk-toggle>Open modal</a>

<UFModal id="my-modal" closable>
    <template #header>Confirm action</template>
    <p>Are you sure you want to continue?</p>
    <template #footer>
        <button class="uk-button uk-modal-close">Cancel</button>
        <button class="uk-button uk-button-danger">Yes, delete</button>
    </template>
</UFModal>
```

#### `UFModalAlert`

A pre-built modal for simple one-action alerts: a title, a message, and a single OK button. No cancel option.

**Props:**
| Prop       | Type     | Default                                              | Description     |
|------------|----------|------------------------------------------------------|-----------------|
| `title`    | `string` | `''`                                                 | Modal title     |
| `prompt`   | `string` | `'Something happened that requires your attention.'` | Body message    |
| `btnLabel` | `string` | `'Ok'`                                               | OK button label |

**Slots:**
| Slot      | Description               |
|-----------|---------------------------|
| `header`  | Overrides the title       |
| `default` | Overrides the body        |
| `footer`  | Overrides the button area |

```vue
<UFModalAlert id="success-alert" title="Done!" prompt="Your changes were saved." />
```

#### `UFModalConfirmation`

A pre-built confirmation dialog with Accept and Cancel buttons. Emits `confirmed` or `cancelled` so you can wire up your logic without managing modal state manually.

**Props:**
| Prop             | Type             | Default                   | Description                                          |
|------------------|------------------|---------------------------|------------------------------------------------------|
| `title`          | `string`         | `'CONFIRMATION'`          | i18n key for the title                               |
| `prompt`         | `string`         | `'CONFIRM_ACTION'`        | i18n key for the body message                        |
| `warning`        | `string`         | `'WARNING_CANNOT_UNDONE'` | i18n key for the warning note (empty string to hide) |
| `acceptLabel`    | `string`         | `'CONFIRM'`               | i18n key for the accept button                       |
| `rejectLabel`    | `string`         | `'CANCEL'`                | i18n key for the cancel button                       |
| `acceptSeverity` | `Severity`       | `Severity.Primary`        | Accept button style                                  |
| `rejectSeverity` | `Severity`       | `Severity.Default`        | Cancel button style                                  |
| `acceptIcon`     | `string \| null` | `'check'`                 | FontAwesome icon for the accept button               |
| `rejectIcon`     | `string \| null` | `'xmark'`                 | FontAwesome icon for the cancel button               |
| `icon`           | `string \| null` | `'triangle-exclamation'`  | Body icon. Pass `null` to hide                       |
| `cancelBtn`      | `boolean`        | `true`                    | Show the cancel button                               |
| `closable`       | `boolean`        | `false`                   | Show × close button                                  |
| `escClose`       | `boolean`        | `true`                    | Close on ESC                                         |
| `bgClose`        | `boolean`        | `true`                    | Close on backdrop click                              |

**Slots:**
| Slot      | Description                      |
|-----------|----------------------------------|
| `header`  | Overrides the title              |
| `prompt`  | Overrides only the body message  |
| `warning` | Overrides only the warning text  |
| `default` | Overrides the entire body        |
| `footer`  | Overrides the entire button area |

**Emits:**
| Emit        | Description                    |
|-------------|--------------------------------|
| `confirmed` | User clicked the accept button |
| `cancelled` | User clicked the cancel button |

> [!NOTE]
> Closing via backdrop click or ESC does **not** emit `cancelled`. If you need to catch all cancel paths, set `bgClose` and `escClose` to `false`.

```vue
<UFModalConfirmation
    id="delete-user"
    title="USER.DELETE"
    prompt="USER.DELETE_CONFIRM"
    warning=""
    acceptLabel="USER.DELETE"
    acceptIcon="trash"
    :rejectIcon="null"
    :acceptSeverity="Severity.Danger"
    @confirmed="deleteUser"
    @cancelled="closeModal" />
```

#### `UFModalPrompt`

A modal with a text input field for collecting user input before an action — like asking for a name or a confirmation phrase. Bind the value with `v-model`.

**Props:**
| Prop          | Type     | Default | Description                  |
|---------------|----------|---------|------------------------------|
| `title`       | `string` | `''`    | Modal title                  |
| `prompt`      | `string` | `''`    | Input field label (i18n key) |
| `placeholder` | `string` | `''`    | Input placeholder (i18n key) |
| `btnLabel`    | `string` | `'OK'`  | Submit button label          |

**Slots:**
| Slot      | Description               |
|-----------|---------------------------|
| `header`  | Overrides the title       |
| `default` | Overrides the entire body |
| `footer`  | Overrides the button area |

```vue
<UFModalPrompt
    id="rename-modal"
    v-model="newName"
    title="Rename item"
    prompt="New name"
    placeholder="e.g. My Report" />
```

### Sprunje Data Tables

The [Sprunje](/database/data-sprunjing) component set is built around `UFSprunjeTable`, which fetches paginated data from a Sprunjer API endpoint and distributes it to nested components via Vue's provide/inject. All other Sprunje components must be used inside `UFSprunjeTable`.

> [!NOTE]
> The Sprunje component set is designed to work with the API structure provided by UserFrosting's Sprunjer system. We'll learn more about Sprunje in the next pages. Just remember that these components exists and are available for you to use whenever you need to display paginated server data in a table.

#### `UFSprunjeTable`

The root component. Fetches, paginates, sorts, and filters server data from a Sprunjer API endpoint. Exposes a `sprunjer` object to slot scopes for advanced control.

**Props:**
| Prop             | Type      | Default    | Description                                        |
|------------------|-----------|------------|----------------------------------------------------|
| `dataUrl`        | `string`  | ✅ required | API endpoint URL                                   |
| `searchColumn`   | `string`  | —          | Enables a search input bound to this column filter |
| `defaultSorts`   | `object`  | `{}`       | Initial sort state, e.g. `{ name: 'asc' }`         |
| `defaultFilters` | `object`  | `{}`       | Initial filter state                               |
| `defaultSize`    | `number`  | `10`       | Initial page size                                  |
| `defaultPage`    | `number`  | `0`        | Initial page (0-indexed)                           |
| `hidePagination` | `boolean` | `false`    | Hide the paginator                                 |
| `hideFilters`    | `boolean` | `false`    | Hide the filter panel toggle                       |

**Slots:**
| Slot          | Scope               | Description                                                      |
|---------------|---------------------|------------------------------------------------------------------|
| `actions`     | `{ sprunjer }`      | Area above the table, left side (e.g. a "Create" button)         |
| `filters`     | `{ sprunjer }`      | Area above the table, right side (custom filter controls)        |
| `header`      | `{ sprunjer }`      | `<thead>` row — place `UFSprunjeHeader` components here          |
| `body`        | `{ row, sprunjer }` | `<tbody>` row per item — place `UFSprunjeColumn` components here |
| `filterPanel` | `{ sprunjer }`      | Extra content in the filter sidebar panel                        |
| `paginator`   | `{ sprunjer }`      | Overrides the default `UFSprunjePaginator`                       |

```vue
<UFSprunjeTable dataUrl="/api/users" searchColumn="name" :defaultSorts="{ name: 'asc' }">
    <template #actions>
        <button class="uk-button uk-button-primary">Add user</button>
    </template>
    <template #header>
        <UFSprunjeHeader sort="name">Name</UFSprunjeHeader>
        <UFSprunjeHeader sort="email">Email</UFSprunjeHeader>
        <UFSprunjeHeader>Actions</UFSprunjeHeader>
    </template>
    <template #body="{ row }">
        <UFSprunjeColumn>{{ row.name }}</UFSprunjeColumn>
        <UFSprunjeColumn>{{ row.email }}</UFSprunjeColumn>
        <UFSprunjeColumn>
            <button class="uk-button uk-button-small">Edit</button>
        </UFSprunjeColumn>
    </template>
</UFSprunjeTable>
```

#### `UFSprunjeHeader`

A `<th>` cell for the table header. When `sort` is provided, it renders as a clickable sortable column with automatic direction indicators (ascending / descending / neutral).

**Props:**
| Prop   | Type     | Default | Description                                          |
|--------|----------|---------|------------------------------------------------------|
| `sort` | `string` | —       | Column key to sort by. Omit for non-sortable columns |

**Slots:**
| Slot      | Description  |
|-----------|--------------|
| `default` | Column label |

```vue
<UFSprunjeHeader sort="created_at">Created</UFSprunjeHeader>
<UFSprunjeHeader>Actions</UFSprunjeHeader>
```

#### `UFSprunjeColumn`

A `<td>` cell for table body rows. Accepts standard HTML attributes (like `class`) via fallthrough.

**Slots:**
| Slot      | Description  |
|-----------|--------------|
| `default` | Cell content |

```vue
<UFSprunjeColumn class="uk-width-1-4">{{ row.name }}</UFSprunjeColumn>
```

#### `UFSprunjeSearch`

A search input that filters the table by a specific column.

**Props:**
| Prop     | Type     | Required | Description                                                 |
|----------|----------|----------|-------------------------------------------------------------|
| `column` | `string` | ✅        | The column/filter key to search on                          |
| `label`  | `string` | —        | Placeholder label. Defaults to a translated "Search" string |

> [!TIP]
> For a simple search, you can also use the `searchColumn` prop directly on `UFSprunjeTable` instead of adding `UFSprunjeSearch` manually.

## Ready-Made Views

Pink-Cupcake and the admin sprinkle also export pre-built page views you can use directly in your routes:

- **Auth views**: `PageLogin`, `PageRegister`, `PageForgotPassword`, and others.
- **Admin views**: `UFAdminDashboardPage`, `UFAdminUsersPage`, `UFAdminRolesPage`, and more from `@userfrosting/sprinkle-admin`. These are used in the admin panel routes defined by the admin sprinkle, but you can also use them directly in your own routes if you want.

You can drop these straight into your route definitions, or study them as solid reference implementations when building similar pages of your own.

## A Quick Layout Example

Here's how Pink-Cupcake components and UIkit classes work together in a typical layout:

```vue
<template>
    <UFNavBar title="Control Panel">
        <template #default>
            <UFNavBarItem :to="{ name: 'dashboard' }" label="Dashboard" />
        </template>
    </UFNavBar>

    <UFSideBar>
        <UFSideBarItem :to="{ name: 'dashboard' }" label="Dashboard" icon="home" />
        <UFSideBarItem :to="{ name: 'reports' }" label="Reports" faIcon="chart-line" />
    </UFSideBar>

    <main class="uk-container uk-margin-top">
        <UFHeaderPage />
        <RouterView />
    </main>
</template>
```

`UFNavBar`, `UFSideBar`, and `UFHeaderPage` are Pink-Cupcake components that define the shell structure. `uk-container` and `uk-margin-top` are UIkit utility classes that handle spacing and layout. Both are doing their job at the same time.
