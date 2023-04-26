---
title: Alerts
metadata:
    description: The `ufAlerts` plugin handles retrieving and rendering alerts and notifications from the alert stream.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

For page loads, as well as requests made by most of UserFrosting's [client-side components](/client-side-code/components), alerts are automatically fetched from the [alert stream](/routes-and-controllers/alert-stream) and rendered for you. However, sometimes you will make your own custom AJAX requests that need to manually fetch and render alerts after the request is complete. To do this, you may create your own instance of the `ufAlerts` plugin.

## Initialization

To initialize the `ufAlerts` plugin, simply call `.ufAlerts()` on the target element where alerts will be displayed. For example, you can do this in the `.fail()` callback for an AJAX request in jQuery:

```js
$.ajax({
    type: 'POST',
    url: site.uri.public + '/api/owls',
    data: {
        species: 'Bubo'
    }
}).fail(function (jqXHR) {
    // Display errors on failure
    var debugAjax = (typeof site !== "undefined") && site.debug.ajax;

    if (debugAjax && jqXHR.responseText) {
        document.write(jqXHR.responseText);
        document.close();
    } else {
        // Destroy any previous instance
        $("#alerts-page").ufAlerts('destroy');
        // Create new instance of ufAlerts
        $("#alerts-page").ufAlerts().ufAlerts('fetch').ufAlerts('render');
    }
});
```

The `fetch` method will retrieve any alerts that were added to the message stream, via the `/alerts` route. The `render` method will then display them in the element that you initialized `ufAlerts` on.

## Methods

Methods can be called after initialization by passing the method name as a string to subsequent calls of `ufAlerts`:

```js
$(el).ufAlerts('<method name>');
```

### `fetch`

Fetch alerts from the message stream resource url.

### `render`

Render all alert messages in the initialized element.

Alerts are rendered using a custom [Handlebars template](/client-side-code/client-side-templating). The default template is located in `core/templates/pages/partials/alerts.html.twig`, and uses Bootstrap 3's [alert](https://getbootstrap.com/docs/3.3/components/#alerts) component to render each message. This partial template is automatically included in the `core/templates/pages/abstract/base.html.twig` template, in the `uf_alerts_template` block.

If you wish you may include your own custom Handlebars template instead, overriding the `uf_alerts_template` block and and specifying its `id` with the `alertTemplateId` option when you initialize `ufAlerts`.

### `push`

This method allows you to add additional alert messages in your client-side code. They will be rendered just like any messages that were retrieved with `fetch` the next time `render` is called:

```js
$("#alerts-page").ufAlerts('push', 'danger', 'You messed up!').ufAlerts('render');
```

### `clear`

This method clears the internally loaded collection of alerts, as well as their rendered HTML from the initialized element:

```js
$("#alerts-page").ufAlerts('clear');
```

### `destroy`

Destroy the `ufAlerts` instance on a DOM element:

```js
$("#alerts-page").ufAlerts('destroy');
```

## Options

### `url`

The absolute URL from which to fetch flash alerts. Defaults to `site.uri.public + '/alerts'`.

### `scrollToTop`

Whether to automatically scroll back to the top of the page after rendering alerts. Defaults to `true`.

### `scrollWhenVisible`

Whether to automatically scroll back to the top of the page even if the alerts are already visible in the current viewport. Only used when `scrollToTop` is `true`. Defaults to `false`.

### `agglomerate`

Set to true to render all alerts in a single bulleted list (`ul/li`), applying styling based on the highest-priority alert being rendered. Defaults to `false`.

### `alertMessageClass`

The CSS class(es) to be applied to each alert message. Defaults to `uf-alert-message`.

### `alertTemplateId`

The id of the Handlebars alert template to use when rendering alerts. Defaults to `uf-alert-template`.
