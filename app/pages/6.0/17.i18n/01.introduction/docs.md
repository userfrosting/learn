---
title: Introduction
metadata:
    description: An introduction to UserFrosting i18n system
    obsolete: true
---

UserFrosting provides a complete framework for translating strings and sentences easily and efficiently. UserFrosting allows you to [define the default locale](/i18n/site-locale#the-default-locale) your UI should present to guest users, while allowing resgistered users to [choose their prefered locale](/i18n/site-locale#the-available-user-locales).

## Why use translation on a single locale app ?

It's important to talk about why this system is important and why you should use it too in your sprinkles, even if you plan on supporting only a single locale in your app.

Let's face it, it would be _waayyyy easier_ to simply hard code every public facing string in your common code. While using the translation system will make it easier to support additional languages in the future, it will also help separate your code from your site content.

Imagine one day you decide to change the term **Users** to **Members**. If you hardcoded everything inside your code, you now have to go through all of the files, look for everwhere the word **User** (and **User_s_** !) is written and change it, one by one, everywhere. Keep in mind, this word is also used in the core code of UserFrosting! This means, if we were to hardcode everything too, that you would have to update all UserFrosting provided files too!

However, having all the _content_ in a single location can help in this situation. Since a common **messages key** is used in your code, you now have to fix only the **localized message**. The same goes if you made a typo somewhere...

As you'll see later, the translation system is also very powerful when it comes to pluralisation. The translation system already has the necessary logic to handle the difference between "1 item" and "10 item**s**".

Finaly, it can also makes it easier for non-coders to review your content. Whether to check for spelling errors or to actually update some fixed content displayed to your user, people with little technical knowledge can understand and edit language files.

## The i18n trio

To better understand how to support multiple locales within your site, we need to start by looking at how UserFrosting handles translation of text.

UserFrosting [i18n module](https://github.com/userfrosting/i18n) is composed of three objects that work together to handle translation duties. Those objects are:

1. Locale
2. Dictionary
3. Translator

A **Locale** is used to create a **Dictionary**, which is then used by the **Translator**. The overall flow can be visualized as :

![](/images/i18n/diagram.png)

### The Locale

The **Locale** object contains all there is to know about the locale itself: The name of the locale (in both English and the localized variant), the authors who provided the translations, the plural rule the locale needs to follow, etc.

All this information can be found in the locale **configuration file**. Like everything with UserFrosting, a locale can be overwritten by any Sprinkle.

See [Creating a custom locale](/i18n/custom-locale) for more information.

### The Dictionary

The **Dictionary** is tied to a specific _locale_. The dictionary's purpose is to return a data matrix composed of keys shared between all locales, called **messages keys**, and the associated translated phrases, called **localized messages**.

The system uses a `KEY` and `VALUE` system, which is stored in standard PHP arrays:

```php
return [
    'MESSAGE_KEY' => 'Localized message',
];
```

This information is stored in **languages files**. These are normal PHP files typically located in `app/sprinkles/{sprinkleName}/locale/{locale}/messages.php` and grouped into folders named after the locale code, as pictured below. Each locale can have as many files as needed (eg. `messages.php`, `foo.php`, `bar.php`, etc.) for easier maintenance. Those files will be merged together at runtime to create a **compiled dictionary** of all the keys available for the translator to use.

**locale/es_ES/example.php**

```php
return[
	"ACCOUNT_SPECIFY_USERNAME"      => "Introduce tu nombre de usuario.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME"  => "Introduce tu nombre público.",
];
```

**locale/en_US/example.php**

```php
return [
	"ACCOUNT_SPECIFY_USERNAME"      => "Please enter your user name.",
    "ACCOUNT_SPECIFY_DISPLAY_NAME"  => "Please enter your display name.",
    "ACCOUNT_SPECIFY_AGE"           => "Please enter your age.",
];
```

Some locales have a parent locale, and each locale's language files will be loaded on top of the parent's one. So for example, since the Spanish version above doesn't have any value for the `ACCOUNT_SPECIFY_AGE` key, the English value would be returned for that key if `en_ES` has `en_US` for parent.

> [!TIP]
> Some Bakery commands can help you view and compare locale Dictionaries. See the [Built-in Commands](/cli/commands#locale-compare) page for more info.

Just like [routes](/routes-and-controllers/front-controller), the names of the files don't matter as they won't overwrite each other. This means two sprinkles can have a `locale/en_US/messages.php` file and **both** will be loaded and **merged** togeter. This means that each subsequently loaded Sprinkle can override translations from previous Sprinkles, or define new ones entirely.

For example, if you want your sprinkle to overwrite a value in the core, you can redefine the same key in your sprinkle :

**app/sprinkles/core/locale/en_US/example.php**

```php
return[
    ...
    "ACCOUNT_SPECIFY_USERNAME"      => "Please enter your user name.",
    ...
];
```

**app/sprinkles/MySite/locale/en_US/MyCustomLanguage.php**

```php
return [
	"ACCOUNT_SPECIFY_USERNAME"      => "Enter your name!",
];
```

When processed using the English locale, `ACCOUNT_SPECIFY_USERNAME` will be linked to the phrase `Enter your name!`.

### The Translator

Finally, the **Translator** use the information from a specific Dictionary to perform the actual association, aka finding the proper _localized messages_ and returning it to the system, while replacing the placeholder with the specified values.

In [the next pages](/i18n/translator), we will see how to actually use the translator in your app.
