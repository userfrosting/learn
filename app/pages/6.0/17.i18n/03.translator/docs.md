---
title: The translator
metadata:
    description: Each sprinkle can overwrite or add new translations keys using translation files
taxonomy:
    category: docs
---

Now that we've [seen the basics](/i18n/introduction), it's time to actually use the translator in code, and learn a little bit more about special features the translator offers, mainly **placeholder**, **pluralization** and **nested keys**.

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
> The translator service contains others public methods that can be useful for you. For example, you can use it to retrieve the associated dictionary and locale. See the [i18n API Guide](https://github.com/userfrosting/i18n/tree/master/docs) for more information.

### In Twig

The translator service is also available as a [Twig function](/templating-with-twig). Placeholders can be passed to the Twig function too:

```
{{ translate("ACCOUNT_SPECIFY_USERNAME") }}
```

### In Javascript

Since the translator is written in PHP, it cannot be used with Javascript yet. In fact, providing dynamic translation with Javascript can be problematic. In most cases, it simpler to either have all your interface text pre-loaded in the page HTML (or Twig) template, or thought a separate AJAX query like the [alert stream](/routes-and-controllers/alert-stream). [Handlebar templates](/client-side-code/client-side-templating) can also be really helpful when dealing with dynamic content.

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

```
{{ translate("ACCOUNT_USER_CHAR_LIMIT", {min: 4, max: 200}) }}
```

## Pluralization

The translator also account for easy pluralization of strings. For a given language, there is a grammatical rule on how to change words depending on the number qualifying the word. Different languages can have different rules. For example, when dealing with an absence of cars, in English you say "**no carS**" (note the **s**), while in French you say **Aucune voiture** (note the **absence** the "s" at the end of "voiture").

The rules associated with a particular language that are used by UserFrosting are based on [Mozilla plural rules](https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals). For example, the **English** locale uses the rule #1, while the **French** locale uses the rule #2. The plural rule a locale will apply is defined in [it's configuration file](/i18n/custom-locale#plural-rule).

In the dictionary, messages keys that support a plural form will have an array as a localized messages instead of a single string. This arrays uses the **rules form** as the key. The right plural form to use is determined by the placeholder value passed as the second parameter of the `translate` function :

```php
"HUNGRY_CATS" => [
	0 => "hungry cats",
	1 => "hungry cat",
	2 => "hungry cats",
]

echo $this->translator->translate("HUNGRY_CATS", 0); // Return "hungry cats"
echo $this->translator->translate("HUNGRY_CATS", 1); // Return "hungry cat"
echo $this->translator->translate("HUNGRY_CATS", 2); // Return "hungry cats"
echo $this->translator->translate("HUNGRY_CATS", 5); // Return "hungry cats"
```

The plural value used to select the right form is defined by default as the `plural` placeholder. This means these two are equivalent :

```php
$this->translator->translate("HUNGRY_CATS", 5);
$this->translator->translate("HUNGRY_CATS", ['plural' => 5]);
```

If no placeholder value is defined, `1` will be used by default. For example, these will return the same result :

```php
$this->translator->translate("HUNGRY_CATS");
$this->translator->translate("HUNGRY_CATS", 1);
$this->translator->translate("HUNGRY_CATS", ['plural' => 1]);
```

The `plural` placeholder can also be used in the localized messages. Note that in this case, it is recommended to add the `X_` prefix to the key to indicate that the plural will be displayed :

```php
"X_HUNGRY_CATS" => [
	0 => "No hungry cats",
	1 => "{{plural}} hungry cat",
	2 => "{{plural}} hungry cats",
]

echo $this->translator->translate("X_HUNGRY_CATS", 0); // Return "No hungry cats"
echo $this->translator->translate("X_HUNGRY_CATS", 1); // Return "1 hungry cat"
echo $this->translator->translate("X_HUNGRY_CATS", 2); // Return "2 hungry cats"
echo $this->translator->translate("X_HUNGRY_CATS", 5); // Return "5 hungry cats"
echo $this->translator->translate("X_HUNGRY_CATS", ['plural': 5]); // Return "5 hungry cats" (equivalent to the previous one)
```

> [!TIP]
> Note that the `plural` placeholder can be overwritten using [handles](#-plural-special-handle).

In this example, you can see that `0` is used as a special form to display `No hungry cats` instead of `0 hungry cats` to create more user friendly message.

> [!WARNING]
> Remember, the **number** defined as the array key **IS NOT** related to the plural value, but to [the plural rule](https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals).
> For example :
> ```php
> "X_HUNGRY_CATS" => [
> 0 => "No hungry cats",
> 1 => "One hungry cat",
> 2 => "{{plural}} hungry cats",
> 5 => "A lot of hungry cats"
> ]
> ```
> With :
> ```php
> echo $this->translator->translate("X_HUNGRY_CATS", 5);
> ```
> Will display "**5 hungry cats**", not "A lot of hungry cats" !

### Plural value with additional placeholders
If you have more than one placeholder, you must pass the plural value in the placeholders, no shortcut possible:

```php
"X_EMOTION_CATS" => [
 0 => "No {{emotion}} cats",
 1 => "One {{emotion}} cat",
 2 => "{{plural}} {{emotion}} cats",
]

echo $this->translator->translate("X_EMOTION_CATS", ['plural': 2, 'emotion': 'hungry']); // Return "2 hungry cats"
echo $this->translator->translate("X_EMOTION_CATS", ['plural': 5, 'emotion': 'angry']); // Return "5 angry cats"
```

### Multiple plural in a string
If a localized string contains more than more plural, for example **X guest(s) and X friend(s) currently online**, you can apply the plural rule to both `guest` and `friends` by [nesting](#nested-keys) the `ONLINE_GUEST` and `ONLINE_FRIEND` keys into `ONLINE_USERS`:

```php
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

$online_guest => $this->translator->translate("ONLINE_GUEST", 1);
$online_friend => $this->translator->translate("ONLINE_FRIEND", 4);
echo $this->translator->translate("ONLINE_USERS", ["guest" => $online_guest, "friend" => $online_friend]);

// RESULT :
// 1 guest and 4 friends currently online
```

> [!NOTE]
> Nested translations can be used when faced with long sentence using multiples sub strings or plural form, but those should be avoided when possible. Shorter or multiple sentences should be preferred instead. Specials [handles](#the-placeholder) can also be useful in those cases.

### `@PLURAL` special handle
The default `plural` default placeholder can be overwritten by the `@PLURAL` handle in the language files. This may be useful if you pass an existing array to the translate function, or using multiple placeholder.

```php
"NB_HUNGRY_CATS" => [
    "@PLURAL" => "nb",
	0 => "No hungry cats",
	1 => "One hungry cat",
	2 => "{{nb}} hungry cats",
]

echo $this->translator->translate("NB_HUNGRY_CATS", 2); // Return "2 hungry cats"
echo $this->translator->translate("NB_HUNGRY_CATS", ['nb': 5]); // Return "5 hungry cats"
```

### One last thing about pluralization...
In some cases, it could be faster and easier to directly access the plural value. For example, when the string will *always* be plural. Consider the following example :

```php
"COLOR" => [
  0 => "colors",
  1 => "color",
  2 => "colors"
],
"COLORS" => "Colors",
```

In this example, `translate("COLOR", 2);` and `translate("COLORS");` will return the same value. This might be true for _English_, but not necessarily for all languages. While there are languages without any form of plural definitions (like Asian languages), some might have even more complicated rules. That's why it's always best to avoid keys like `COLORS` if you plan to translate to more than one language. This is also true with the `0` value that can be different across different language, but can also be handled differently depending on the message you want to display (Ex.: `No colors` instead of `0 colors`).

## Nested keys
Nested keys can be defined in language files for easier navigation of lists or to distinguish two items with common keys. For example:

```php
return [
  "COLOR" => [
    "BLACK" => "black",
    "RED"   => "red",
    "WHITE" => "white"
  ]
];
```

Nested keys can be accessed using _dot notation_, eg. `translate('COLOR.BLACK')` will return `black`. Nested keys are also useful when multiple *master keys* share the same context. For example, let's consider :

```php
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

$method = Input::get(); // return $method = "A";
echo $this->translator->translate("METHOD_$method.TITLE"); // Print "Scénario A"
```

In this case, it would be cleaner to define everything this way :

```php
return [
	"METHOD" => [
        "A" => [
            "TITLE" => "Scénario A",
            "DESCRIPTION" => "..."
        ],
        "B" => [
            "TITLE" => "Scénario B",
            "DESCRIPTION" => "..."
        ]
    ],
];

$method = Input::get(); // return $method = "A";
echo $this->translator->translate("METHOD.$method.TITLE"); // Print "Scénario A"
```

In the future, if `METHOD` where to change to something else, or if you want add a `METHOD.CHOOSE` key, it would make it easier to navigate your dictionary code.

### Nested keys and plural forms

Of courses, nested keys and plural rules can live together inside the same master key. For example :

```php
"COLOR" => [
    //Substrings
    "BLACK" => "black",
    "RED"   => "red",
    "WHITE" => "white",

    //Plurals
    1 => "color",
    2 => "colors"
]

echo $this->translator->translate("COLOR.BLACK");    // black
echo $this->translator->translate("COLOR", 2);       // colors
echo $this->translator->translate("COLOR.WHITE", 2); // white
```

### `@TRANSLATION` handle

If you want to define a value for the parent key as well as having nested keys, for example being able to use both `ACCOUNT` and `ACCOUNT.ALT`, you can use the `@TRANSLATION` handle which will be used to create an alias to the parent key :

```php
return [
    "ACCOUNT" => [
        "@TRANSLATION" => "Account",
        "ALT" => "Profile"
    ]
];


$this->translator->translate('ACCOUNT')              // Return "Account"
$this->translator->translate('ACCOUNT.@TRANSLATION') // Return "Account"
$this->translator->translate('ACCOUNT.ALT');         // Return "Profile"
```

When `@TRANSLATION` is used with plural forms, omitting the second argument of the `translate` function will change the default behavior. Instead of returning the correct form for a value of **1**, the `@TRANSLATION` value will be returned instead. For example:

```php
"X_HUNGRY_CATS" => [
    "@TRANSLATION" => "Hungry cats",

	0 => "No hungry cats",
	1 => "{{plural}} hungry cat",
	2 => "{{plural}} hungry cats",
];

$this->translator->translate("X_HUNGRY_CATS");      // Hungry cats
$this->translator->translate("X_HUNGRY_CATS", 1);   // 1 hungry cat
```

## The `&` placeholder

When a placeholder identifier starts with the `&` character, it tells the translator to directly replace the placeholder with the right language key (if found). Note that this is CASE SENSITIVE and, as with the other handles, all placeholders defined in the main translation function are passed to all child translations.

This behavior is useful when you don't want to translate the same word over and over again. It can also be used in complex translations with plural forms, but be careful when using this with plurals as the plural value is passed to all child translation and can cause conflict (See [Example of a complex translation](#example-of-a-complex-translation)).


```php
"MY_CATS" => [
    1 => "my cat",
    2 => "my {{plural}} cats"
];

"I_LOVE_MY_CATS" => "I love {{&MY_CATS}}";

$this->translator->translate('I_LOVE_MY_CATS', 3); //Return "I love my 3 cats"
```

In this example, `{{&MY_CATS}}` gets replaced with the value of `MY_CATS` to create `I love my {{plural}} cats`. Since there are 3 cats, the rule #2 is selected. So the string becomes `I love my 3 cats`.


> [!NOTE]
> This behavior can be overwritten if you pass a placeholder with the same key to the translate function :
> ```php
> $this->translator->translate('I_LOVE_MY_CATS', [
> "plural" => 3,
> "&MY_CATS" => "my dogs"
> ]);
> // RESULT :
> // I love my dogs
> ```

### Plural adjectives

Since the other placeholders, including the plural value(s), are also being passed to the sub translation, it can be useful for languages like French where the adjectives can also be pluralizable. For example, the sentence "**I have 3 white catS**" would become "**J'ai 3 chatS blancS**" in French. Notice the **S** on both the color **blanc** and the animal **chatS**? One developer could be tempted to do this:

```php
$colorString = $this->translator->translate('COLOR.WHITE'); // blanc
echo $this->translator->translate('MY_CATS', [
    "plural" => 3,
    "color" => $colorString
);
```

While this would work in English because the color isn't pluralizable, it won't in French. We'll end up with "**J'ai 3 chatS blanc**" (No **S** on the color).

To make it work in both language, we need to call the translation and pass the color key as a placeholder using the `&` prefix :

```php
$this->translator->translate('MY_CATS', [
    "plural" => 3,
    "color" => "&COLOR.WHITE"
]);
```

The language files for both languages in this case would be:

**English**:
```php
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

**French**:
```php
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

## Example of a complex translation

When all of this is put together, it's possible to create a really complex translation scenario. But keep in mind, it's usually easier to keep things simple with short sentences !

**English Dictionary**:
```php
return [
    "COMPLEX_STRING" => "There's {{&X_CHILD}} and {{&X_ADULT}} in the {{color}} {{&CAR.FULL_MODEL}}",
    "X_CHILD" => [
        "@PLURAL" => "nb_child",
    	0 => "no children",
    	1 => "a child",
    	2 => "{{plural}} children",
    ],
    "X_ADULT" => [
        "@PLURAL" => "nb_adult",
    	0 => "no adults",
    	1 => "an adult",
    	2 => "{{nb_adult}} adults",
    ],
    "CAR" => [
        "FULL_MODEL" => "{{make}} {{model}} {{year}}"
    ],
    "COLOR" => [
        "BLACK" => "black",
        "RED" => "red",
        "WHITE" => "white"
    ]
];
```

**French Dictionary**:
```php
return [
    "COMPLEX_STRING" => "Il y a {{&X_CHILD}} et {{&X_ADULT}} dans la {{&CAR.FULL_MODEL}} {{color}}",
    "X_CHILD" => [
        "@PLURAL" => "nb_child",
    	0 => "aucun enfant",
    	1 => "un enfant",
    	2 => "{{plural}} enfants",
    ],
    "X_ADULT" => [
        "@PLURAL" => "nb_adult",
    	0 => "aucun adulte",
    	1 => "un adulte",
    	2 => "{{nb_adult}} adultes",
    ],
    // CAR can be omitted as same in French and english
    "COLOR" => [
        "BLACK" => "noir",
        "RED"   => "rouge",
        "WHITE" => "blanc"
    ]
];
```

**Translation**:

```php
$carMake = "Honda";

echo $translator->translate("COMPLEX_STRING", [
    "nb_child"  => 1,
    "nb_adult"  => 0,
    "color"     => "&COLOR.RED",
    "make"      => $carMake,
    "model"     => "Civic",
    "year"      => 1993
]);
```

**Result with English Dictionary**:
```txt
There's a child and no adults in the red Honda Civic 1993
```

**Result with French Dictionary**:
```txt
Il y a un enfant et aucun adulte dans la Honda Civic 1993 rouge
```
