---
title: Internationalization
metadata:
    description: Internationalization consists of the translation files used to translate pages of your web application. UserFrosting provides a framework for translating strings and sentences easily and efficiently.
taxonomy:
    category: docs
---

UserFrosting comes with a complete internationalization system. This system allows you to translate your pages in any language you want. The internationalization service uses the [i18n module](https://github.com/userfrosting/i18n) to handles translation tasks for UserFrosting. The internationalization system also includes a powerful pluralization handling.

Translating strings, or sentences, is as easy as assigning localized sentences to a common _translation key_. To achieve this, two things are used: the translation files and the `MessageTranslator`. 

## The translation files

The `locale` directory contains [translation files](/sprinkles/contents#locale) for your Sprinkle.  Like configuration files, translation files simply return an associative array mapping _language keys_ to _localized messages_.

Just as with configuration files, UserFrosting will recursively merge translation files for the currently selected language(s) from each loaded Sprinkle.  This means that each subsequently loaded Sprinkle can override translations from previous Sprinkles, or define new ones entirely.

Translation files can be found in each Sprinkle's `locale/` directory and accessed using the `locale://` **stream wrapper**, just like the `assets://` one. Here's an example of a translation file for both Spanish and English languages:


**locale/es_ES/example.php**

```
return array(
	"ACCOUNT_SPECIFY_USERNAME" => "Introduce tu nombre de usuario.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Introduce tu nombre público.",
);
```

**locale/en_US/example.php**

```
return array(
	"ACCOUNT_SPECIFY_USERNAME" => "Please enter your user name.",
	"ACCOUNT_SPECIFY_DISPLAY_NAME" => "Please enter your display name.",
);
```

>>>>> Locale files are grouped into folders named after the locale code, as pictured above. This means `locale/en_US/` can contain multiple locale files allowing you to split your translation keys accross multiple files for easier maintenance.

Messages may optionally have placeholders. The placeholders allows you to insert variables in a message, avoiding the need for a unique message for every possible solution. For example:


```
return array(
	"ACCOUNT_USER_CHAR_LIMIT" => "Tu nombre de usuario debe estar entre {{min}} y {{max}} caracteres de longitud."
);

...

return array(
	"ACCOUNT_USER_CHAR_LIMIT" => "Your user name must be between {{min}} and {{max}} characters in length."
);
```


## Translating strings

Translating string is just a matter of asking the `MessageTranslator`, via the `translator` service, to return the localized version of a key based on the site or user locale as follows:
```
$this->ci->translator->translate($hook, $params);
``` 

Where `$this->ci` is the [DI container](/services/the-di-container), `$hook` the _language keys_ you want to display and `$params` the placeholders value. For example: 

```
echo $this->ci->translator->translate("ACCOUNT_USER_CHAR_LIMIT", [
    "min" => 4,
    "max" => 200
]);

// Returns "Tu nombre de usuario debe estar entre 4 y 200 caracteres de longitud."
```

The translator service is also available as a [Twig function](/templating-with-twig). Placeholders can be passed to the Twig function too:

```
{{ translate("ACCOUNT_SPECIFY_USERNAME") }}

{{ translate("ACCOUNT_USER_CHAR_LIMIT", {min: 4, max: 200}) }}
```


## Setting up the site language

The site default languages can be set in the [config](/configuration/config-files) parameters. The `site.locales` contains the locale to use for global, guest users. Multiple locales can be listed, separated by commas, to indicate the locale precedence order. For example, `'locales' => 'en_US, fr_FR'` means that the _French_ language will be loaded first and if the requested key doesn't exist in French, it will try to use the _English_ one instead. 

>>>>>> A user can also use its own language. This is defined in the user's profile.


## Pluralization

The plural system allows for easy pluralization of strings. For a given language, there is a grammatical rule on how to change words depending on the number qualifying the word. Different languages can have different rules. For example, in English you say `no cars` (note the **plural** `cars`) while in French you say `Aucune voiture` (note the **singular** `voiture`). 

The rule associated with a particular language is based on [Mozilla plural rules](https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals). The language plural rule is defined in the `@PLURAL_RULE` key. So in the **English** file, you should find `"@PLURAL_RULE" => 1` and in the **French** file `"@PLURAL_RULE" => 2`. Those should be set in the `core` Sprinkle and you don't need to change them, unless if you’re adding a new language to UserFrosting. 

Strings with plural forms are defined as sub arrays with the **rules** as the key. The right plural form is determined by the plural value passed as the second parameter of the `translate` function :
```
"HUNGRY_CATS" => [
	0 => "hungry cats",
	1 => "hungry cat",
	2 => "hungry cats",
]

echo $this->ci->translator->translate("HUNGRY_CATS", 0); // Return "hungry cats"
echo $this->ci->translator->translate("HUNGRY_CATS", 1); // Return "hungry cat"
echo $this->ci->translator->translate("HUNGRY_CATS", 2); // Return "hungry cats"
echo $this->ci->translator->translate("HUNGRY_CATS", 5); // Return "hungry cats"
```

The plural value used to select the right form is defined by default in the `plural` placeholder. This means that `$this->ci->translator->translate("HUNGRY_CATS", 5)` is equivalent to `$this->ci->translator->translate("HUNGRY_CATS", ['plural' => 5])`. The `plural` placeholder can also be used in the string definition. Note that in this case, it is recommended using the `X_` prefix to indicate that the plural will be displayed :

```
"X_HUNGRY_CATS" => [
	0 => "No hungry cats",
	1 => "{{plural}} hungry cat",
	2 => "{{plural}} hungry cats",
]

echo $this->ci->translator->translate("X_HUNGRY_CATS", 0); // Return "No hungry cats"
echo $this->ci->translator->translate("X_HUNGRY_CATS", 1); // Return "1 hungry cat"
echo $this->ci->translator->translate("X_HUNGRY_CATS", 2); // Return "2 hungry cats"
echo $this->ci->translator->translate("X_HUNGRY_CATS", 5); // Return "5 hungry cats"
echo $this->ci->translator->translate("X_HUNGRY_CATS", ['plural': 5]); // Return "5 hungry cats" (equivalent to the previous one)
```

In this example, you can see that `0` is used as a special rule to display `No hungry cats` instead of `0 hungry cats` to create more user friendly strings. Note that the `plural` placeholder can be overwritten using [handles](#plural).

When the first argument of the `translate` function points to a plural key in the language definition files and the second parameter is omitted, the plural value will be `1` by default unless a `@TRANSLATION` key is defined (See [Handles](#handles)). In the previous example, `$this->ci->translator->translate("X_HUNGRY_CATS", 1)` is equivalent to `$this->ci->translator->translate("X_HUNGRY_CATS")`.

### Plural value with placeholders
If you have more than one placeholder, then you must pass the plural value in the placeholders (no shortcut possible).

```
"X_EMOTION_CATS" => [
 0 => "No {{emotion}} cats",
 1 => "One {{emotion}} cat",
 2 => "{{plural}} {{emotion}} cats",
]

echo $this->ci->translator->translate("X_EMOTION_CATS", ['plural': 2, 'emotion': 'hungry']); // Return "2 hungry cats"
echo $this->ci->translator->translate("X_EMOTION_CATS", ['plural': 5, 'emotion': 'angry']); // Return "5 angry cats"
```

### Multiple plural in a string
If a localized string contains more than more plural, for example `1 guest and 4 friends currently online`, you can apply the plural rule to both `guest` and `friends` by nesting the `ONLINE_GUEST` and `ONLINE_FRIEND` keys into `ONLINE_USERS`:
```
"ONLINE_GUEST" => [
	0 => "0 guests",
	1 => "1 guest",
	2 => "{{plural}} guests"
],

"ONLINE_FRIEND" => [
	0 => "0 friends",
	1 => "1 friend",
	2 => "{{plural}} friends"
],

"ONLINE_USERS" => "{{guest}} and {{friend}} currently online",

[...]

$online_guest => $this->ci->translator->translate("ONLINE_GUEST", 1);
$online_friend => $this->ci->translator->translate("ONLINE_FRIEND", 4);
echo $this->ci->translator->translate("ONLINE_USERS", ["guest" => $online_guest, "friend" => $online_friend]); // Returns "1 guest and 4 friends currently online"
```

>>>>> Nested translations can be used when faced with long sentence using multiples sub strings or plural form, but those should be avoided when possible. Shorter or multiple sentences should be preferred instead. Specials [handles](#handles) can also be useful in those cases.

### Numbers are rules, not limits !
>>>> Remember, the **number** defined in the language files **IS NOT** related to the plural value, but to [the plural rule](https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals). **So this is completely WRONG** :

```
"X_HUNGRY_CATS" => [
	0 => "No hungry cats",
	1 => "One hungry cat",
	2 => "{{plural}} hungry cats",
	5 => "A lot of hungry cats"
]

echo $this->ci->translator->translate("X_HUNGRY_CATS", 2); // Return "2 hungry cats"
echo $this->ci->translator->translate("X_HUNGRY_CATS", 5); // Return "5 hungry cats", NOT "A lot of hungry cats"!
```

### One last thing about pluralization...
In some cases, it could be faster and easier to directly access the plural value. For example, when the string will *always* be plural. Consider the following example :
```
"COLOR" => [
  0 => "colors",
  1 => "color",
  2 => "colors"
],
"COLORS" => "Colors",
```
In this example, `$this->ci->translator->translate("COLOR", 2);` and `$this->ci->translator->translate("COLORS");` will return the same value. This might be true for _English_, but not necessarily for all languages. While languages without any form of plural definitions (like Asian languages) define something like `"COLOR" => "Color"` and `"COLORS" => "Color"`, some might have even more complicated rules. That's why it's always best to avoid keys like `COLORS` if you plan to translate to more than one language. This is also true with the `0` value that can be different across different language, but can also be handled differently depending on the message you want to display (Ex.: `No colors` instead of `0 colors`).


## Sub keys
Sub keys can be defined in language files for easier navigation of lists or to distinguish two items with common keys. For example:

```
return [
  "COLOR" => [
    "BLACK" => "black",
    "RED" => "red",
    "WHITE" => "white"
  ]
];
```
Sub keys can be accessed using _dot syntax_. So `$this->ci->translator->translate('COLOR.BLACK')` will return `black`. Sub keys are also useful when multiple *master keys* share the same sub keys:

```
return [
	"METHOD_A" => [
		"TITLE" => "Scénario A",
		"DESCRIPTION" => "..."
	],
	"METHOD_B" => [
		"TITLE" => "Scénario B",
		"DESCRIPTION" => "..."
	]
];

$method = Method->get(); // return $method = "METHOD_A";
echo $this->ci->translator->translate("$method.TITLE"); // Print "Scénario A"
```

Of courses, sub keys and plural rules can live together inside the same master key :
```
"COLOR" => [
    //Substrings
    "BLACK" => "black",
    "RED" => "red",
    "WHITE" => "white",

    //Plurals
    1 => "color",
    2 => "colors"
]
```

## Handles
Some special handles can be defined in the languages files to modify the default behavior of the translator. These handles use the `@` prefix.

### `@PLURAL_RULE`
See [Pluralization](#pluralization).

### `@TRANSLATION`
If you want to give a value for the top level key, you can use the `@TRANSLATION` handle which will create an alias `TOP_KEY` and point it to `TOP_KEY.@TRANSLATION`:
```
return [
    "ACCOUNT" => [
        "@TRANSLATION" => "Account",
        "ALT" => "Profile"
    ]
];


$this->ci->translator->translate('ACCOUNT') //Return "Account"
$this->ci->translator->translate('ACCOUNT.@TRANSLATION') //Return "Account"
$this->ci->translator->translate('ACCOUNT.ALT'); //Return "Profile"
```

>>> When `@TRANSLATION` is used with plural rules, omiting the second argument of the `translate` function will change the result. `1` will not be used as a plural value to determine which rule we chose. The `@TRANSLATION` value will be returned instead. For example, using the following keys, `$this->ci->translator->translate("X_HUNGRY_CATS");` will return `Hungry cats`. Remove the `@TRANSLATION` handle and the same `$this->ci->translator->translate("X_HUNGRY_CATS");` will now return `1 hungry cat` :

```
"X_HUNGRY_CATS" => [
    "@TRANSLATION => "Hungry cats",
	0 => "No hungry cats",
	1 => "{{plural}} hungry cat",
	2 => "{{plural}} hungry cats",
]
```
 

### `@PLURAL`
The default `plural` default placeholder can be overwritten by the `@PLURAL` handle in the language files. This may be useful if you pass an existing array to the translate function.

```
"NB_HUNGRY_CATS" => [
    "@PLURAL" => "nb",
	0 => "No hungry cats",
	1 => "One hungry cat",
	2 => "{{nb}} hungry cats",
]

echo $this->ci->translator->translate("NB_HUNGRY_CATS", 2); // Return "2 hungry cats"
echo $this->ci->translator->translate("NB_HUNGRY_CATS", ['nb': 5]); // Return "5 hungry cats"
```

### The `&` placeholder
When a placeholder name starts with the `&` character in translation files, or the value of a placeholder starts with this same `&` character, it tells the translator to directly replace the placeholder with the right language key (if found). Note that this is CASE SENSITIVE and, as with the other handles, all placeholders defined in the main translation function are passed to all child translations. This is useful when you don't want to translate the same word over and over again in the same language file or with complex translations with plural values. Be careful when using this with plurals as the plural value is passed to all child translation and can cause conflict (See [Example of a complex translation](#example-of-a-complex-translation)). Example:
```
"MY_CATS" => [
    1 => "my cat",
    2 => "my {{plural}} cats"
];
"I_LOVE_MY_CATS" => "I love {{&MY_CATS}}";

$this->ci->translator->translate('I_LOVE_MY_CATS', 3); //Return "I love my 3 cats"
```
In this example, `{{&MY_CATS}}` gets replaced with the `MY_CATS` and since there are 3 cats, the n° 2 rule is selected. So the string becomes `I love my {{plural}} cats` which then becomes `I love my 3 cats`.


>>> Since this is the last thing handled by the translator, this behaviour can be overwritten by the function call: 
`$this->ci->translator->translate('I_LOVE_MY_CATS', ["plural" => 3, "&MY_CATS" => "my 3 dogs"); //Return "I love my 3 dogs"`

Since the other placeholders, including the plural value(s) are also being passed to the sub translation, it can be useful for languages like French where the adjectives can also be pluralizable. Consider this sentence : `I have 3 white catS`. In French, we would say `J'ai 3 chatS blancS`. Notice the **S** on the color **blanc**? One developer could be tempted to do this in an English context :

```
$colorString = $this->ci->translator->translate('COLOR.WHITE');
echo $this->ci->translator->translate('MY_CATS', ["plural" => 3, "color" => $colorString);
```

While this would work in English because the color isn't pluralizable, it won't in French. We'll end up with `J'ai 3 chatS blanc` (No **S** on the color). We need to call the translation and pass the color key as a placeholder using the `&` prefix : `$this->ci->translator->translate('MY_CATS', ["plural" => 3, "color" => "&COLOR.WHITE"]);`. The language files for both languages in this case would be:

_English_
```
"COLOR" => [
    "RED" => "red",
    "WHITE" => "white",
    [...]
];

"MY_CATS" => [
    0 => "I have no cats",
    1 => "I have a {{color}} cat",
    2 => "I have {{plural}} {{color}} cats"
];
```

_French_
```
"COLOR" => [
    "RED" => [
        1 => "rouge",
        2 => "rouges"
    "WHITE" => [
        1 => "blanc",
        2 =. "blancs"
    ].
    [...]
];

"MY_CATS" => [
    0 => "I have no cats",
    1 => "I have a {{color}} cat",
    2 => "I have {{plural}} {{color}} cats"
];
```
