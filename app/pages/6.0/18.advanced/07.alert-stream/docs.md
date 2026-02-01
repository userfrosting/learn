---
title: The Alert Stream
description: The alert stream is UserFrosting's implementation of a flash messaging system, and is used to display error or success notifications to the end user.
wip: true
---

The **alert stream** is UserFrosting's implementation of a flash messaging system, and is used to display error or success notifications to the end user.

Rather than including alerts directly in a request's response, they are persisted through a separate `AlertStream` interface. They can then be retrieved by making a separate request to `/alerts`, which will return a JSON object containing each alert message and its corresponding type:

![A diagram of the alert creation and retrieval process.](images/alert-stream.png)

You may wonder why we have this complicated process and use a separate request to retrieve alerts, rather than embedding them directly in the original response. The reason is that sometimes you may not be ready to immediately display the alerts after you receive the original request's response. For example, you may have a situation where you need to redirect or refresh the page before displaying alerts.

By storing the alerts in this way, they can be persisted between page loads and other types of non-AJAX requests.

## Alert types

Every alert consists of a message and a corresponding type. The recommended types are based on the styling classes used on the client side by Bootstrap:

- `danger`
- `warning`
- `success`
- `info`

Of course, you are free to define you own custom alert types as well.

## Server-side

### Adding messages to the alert stream

You can add messages to the alert stream in your controller by using the `alerts` service. You can inject the service in your class through Autowiring or Annotation injection on the `UserFrosting\Alert\AlertStream` class:

```php
use DI\Attribute\Inject;
use UserFrosting\Alert\AlertStream;

// ...

#[Inject]
protected AlertStream $alert;

// ...

$this->alert->addMessage('success', 'Your owl has successfully captured another vole!');
```

To generate [translated](i18n) messages, simply use a translation key as the message. Placeholder values can be specified in by passing an array as the third argument:

```php
$this->alert->addMessage('danger', 'OWLS.INSUFFICENT_VOLES', [
    'voles_needed' => 7
]);
```

### Retrieving messages

Message retrieval is already implemented for you via the `/alerts` route definition and the `UserFrosting\Sprinkle\Core\Controller\AlertsController` controller. However if for some reason you need to manually retrieve alerts elsewhere in your server-side code, you can use the `getAndClearMessages` method. This method will automatically remove all alerts from the alert stream after being called.

```php
$messages = $this->alert->getAndClearMessages();
```

**Output:**
```json
[
    [
        "type" => "success"
        "message" => "Your owl has successfully captured another vole!"
    ],
    [
        "type" => "danger"
        "message" => "You still need 7 more voles!"
    ]
]
```

To retrieve messages _without_ removing them, use the `messages()` method instead. To manually clear the messages, use the `resetMessageStream()` method.

### Alert persistence

By default, alerts are stored in the user's [session](advanced/sessions). They are stored here under the `site.alerts` key.

Alternatively, you may choose to store alerts in the [user cache](advanced/caching/usage#user-cache) instead. To do this, set value of `alert.storage` in your [configuration file](configuration/config-files) to `cache`.

> [!IMPORTANT]
> If you are having issues with alerts not being properly cleared from the alert stream after retrieval, try using the `cache` option for alert persistence. See [this issue](https://github.com/userfrosting/UserFrosting/issues/633) for an explanation of the problem.

## Client-side

In your client-side code, alerts are primarily handled by the `ufAlerts` jQuery plugin. The `/core/assets/userfrosting/js/uf-init.js` Javascript asset, which is loaded as part of the `js/main` [asset bundle](asset-management/asset-bundles), will look for an element with an `id` of `alerts-page` and automatically fetch and render any alerts into this element when a page is first loaded.

The `/pages/abstract/default.html.twig` template in the Admin-LTE sprinkle and the `/pages/abstract/base.html.twig` template in the Admin sprinkle both contain a `div` element with `id="alerts-page"`. For pages that do not extend either template, you will need to include the following wherever you want alerts to appear:

```html
<div id="alerts-page"></div>
```

For details on how to use the `ufAlerts` plugin manually, see the documentation in [client side components](client-side-code/components/alerts).
