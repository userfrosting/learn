---
title: Components
metadata:
    description: UserFrosting comes with a number of custom jQuery plugins that simplify the interactions between the client-side components and the backend.
taxonomy:
    category: docs
---

UserFrosting also comes with a number of [custom jQuery plugins](/client-side-code/components) that simplify the interactions between the client-side components and the backend.  These can all be found in the `core` Sprinkle, under `assets/local/core/js/`.

### ufAlerts

Fetches and renders alerts from the [alert stream](/routes-and-controllers/alert-stream).

### ufCollection

A client-side widget that allows you to easily associate related entities in a one-to-many or many-to-many relationship by selecting them from a dropdown menu.

For example, both the "user roles" and "role permissions" interfaces use this plugin:

![ufCollection widget as used for the "user role" management interface.](/images/uf-collection.png)

See the documentation on [collections](/client-side-code/components/collections) for more information on using this plugin.

### ufCopy

Helper to generate a tooltip alert for clipboard.js.

![ufCopy widget.](/images/uf-copy.png)

**Markup:**

```html
<div class="js-copy-container">
    <span class="js-copy-target">{{row.email}}</span>
    <button class="btn btn-xs js-copy-trigger"><i class="fa fa-copy"></i></button>
</div>
```

### ufForm

A convenient wrapper for AJAX form submission.  Handles validation, loading icon during the submission process, and automatically fetching and displaying error messages after a failed submission.

See the section on [forms](/client-side-code/components/forms) for more information on using this plugin.

### ufModal

Renders and displays modal windows that dynamically their fetch content from a specified URL.  Very useful for pop-up forms and dialog boxes.

### ufTable

A wrapper for [Tablesorter](https://mottie.github.io/tablesorter/docs/) that automatically fetches JSON data from a specified API endpoint, and dynamically builds paginated, sorted, filtered views on the fly.  Very useful as a quick-and-easy way to get data from your database to the client.

See the section on [tables](/client-side-code/components/tables) for more information on using this plugin.
