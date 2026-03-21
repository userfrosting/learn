---
title: Using the translator
description: Learn how to use the translator in PHP, Twig, and Vue.js to display localized strings
---

Now that we've [seen the basics](/i18n/introduction), it's time to actually use the translator in code. This page covers how to call the translator service in different contexts (PHP, Twig, and Vue.js) and how to use basic placeholders.

For more advanced features like pluralization, nested keys, and special handles, see [Advanced Translation Features](/i18n/advanced-features).

## Using the translator service

Translating strings is just a matter of asking the `Translator`, via the `translator` service, to return the localized version of a key based on the site or user locale as follows:

```php
$this->translator->translate($key);
```

Where `$this->translator` is an instance of `\UserFrosting\I18n\Translator` (probably injected) and `$key` the _language keys_ you want to display. For example:

```php
echo $this->translator->translate("ACCOUNT_SPECIFY_USERNAME");

// RESULT:
// Please enter your user name.
```

The current locale will be automatically defined and the associated dictionary automatically loaded by UserFrosting.

> [!TIP]
> The translator service contains others public methods that can be useful for you. For example, you can use it to retrieve the associated dictionary and locale. See the [i18n API documentation](https://github.com/userfrosting/framework/tree/main/packages/framework/src/I18n) for more information.

### In Twig

The translator service is also available as a [Twig function](/templating-with-twig). Placeholders can be passed to the Twig function too:

```
{{ translate("ACCOUNT_SPECIFY_USERNAME") }}
```

### In Vue.js

The translator is available in Vue.js through the `useTranslator` composable store from `@userfrosting/sprinkle-core/stores`. The translator automatically loads the user's locale dictionary via an API call and provides full translation capabilities with placeholders, pluralization, nested keys, and date formatting.

#### Available methods

The `useTranslator` composable provides:

- **`translate(key, placeholders?)`** - Translate a message key with optional placeholders
- **`translateDate(date, format?)`** - Format a date using Luxon with the user's locale
- **`getDateTime(date)`** - Get a Luxon DateTime object with the user's locale
- **`getPluralForm(value, rule?)`** - Get the correct plural form for a numeric value
- **`load()`** - Manually load/reload the dictionary from the API

The translator supports all the same features as the PHP version: placeholders, pluralization, nested keys, special handles (`@PLURAL`, `@TRANSLATION`), and the `&` placeholder for referencing other translation keys.

#### Using the composable

Import and use the translator in your Vue components:

```vue
<script setup lang="ts">
import { useTranslator } from '@userfrosting/sprinkle-core/stores'

const { translate } = useTranslator()

// Translate a key
const username = translate('USERNAME')

// With placeholders
const message = translate('WELCOME_TO', { title: 'UserFrosting' })

// With pluralization
const carsMessage = translate('X_CARS', 5)
</script>
```

#### Date formatting

The `translateDate` method uses [Luxon](https://moment.github.io/luxon/) for date formatting:

```vue
<script setup>
import { DateTime } from 'luxon'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'

const { translateDate } = useTranslator()

// Using preset formats
const formatted1 = translateDate(date, DateTime.DATETIME_MED) // "Feb 2, 2025, 9:42 AM"
const formatted2 = translateDate(date, 'DDD') // "February 2, 2025"

// Using custom token format
const formatted3 = translateDate(date, 'yyyy-MM-dd') // "2025-02-02"
</script>
```

See the [Luxon documentation](https://moment.github.io/luxon/#/formatting) for more formatting options.

#### Using global properties

The translator functions are also available as global properties in all Vue templates. The `$t` function is an alias for `translate()`, and `$tdate` is an alias for `translateDate()`:

```vue
<template>
    <h1>{{ $t('WELCOME_TO', { title: 'UserFrosting' }) }}</h1>
    <p>{{ $t('X_CARS', 5) }}</p>
    <p class="date">{{ $tdate(DateTime.now().toISO()) }}</p>
</template>
```

## Placeholders

Messages may optionally have placeholders. The placeholders allows you to insert variables in a message, avoiding the need for a unique message for every possible solution. For example:

```php
return [
	"ACCOUNT_USER_CHAR_LIMIT" => "Your user name must be between {{min}} and {{max}} characters in length."
];
```

Just like Twig and Handlebar, placeholders are represented using the `{{double-mustache}}` notation. To fill in the variables, we can pass an array to the second, optional, parameter of the _translate_ method. For example:

```php
echo $this->translator->translate("ACCOUNT_USER_CHAR_LIMIT", [
    "min" => 4,
    "max" => 200
]);

// Returns "Your user name must be between 4 and 200 characters in length."
```
The same can be done with the Twig function :

```twig
{{ translate("ACCOUNT_USER_CHAR_LIMIT", {min: 4, max: 200}) }}
```

And in Vue.js:

```vue
<script setup>
import { useTranslator } from '@userfrosting/sprinkle-core/stores'
const { translate } = useTranslator()

const message = translate('ACCOUNT_USER_CHAR_LIMIT', { min: 4, max: 200 })
</script>

<template>
    <p>{{ $t('ACCOUNT_USER_CHAR_LIMIT', { min: 4, max: 200 }) }}</p>
</template>
```

## Next steps

Now that you know how to use the translator, explore the [Advanced Translation Features](/i18n/advanced-features) to learn about:

- **Pluralization** - Automatically handle singular/plural forms based on Mozilla plural rules
- **Nested keys** - Organize translations with dot notation
- **Special handles** - Use `@PLURAL` and `@TRANSLATION` for advanced scenarios
- **The `&` placeholder** - Reference other translation keys to avoid repetition
- **Complex translations** - Combine multiple features for sophisticated multilingual content
