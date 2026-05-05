---
title: Adding localizations
metadata:
    description: Adding localization to the page, making the page available in multiple languages.
taxonomy:
    category: docs
---

This section covers situations where you have an internationalized application - i.e., the text will need to be displayed in different languages for different users. We will first need to change the hardcoded English strings to localization keys, which create an extra layer of abstraction by mapping a common identifier to the corresponding English string. Once this is done, we'll add French translations for these same identifiers.

The first thing is to swap out any hard-coded strings for localization keys, and then map these keys to English translations. At this point we'll need to change the following: The page title, the sidebar menu entry, the box title and the table header.

Let's start with the sidebar menu. Here we change the words "List of Pastries" to `{{translate('PASTRIES.LIST')}}` (We'll map the `PASTRIES.LIST` localization key later).

**app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig**:

- _Find:_
```html
<a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>List of Pastries</span></a>
```

- _Replace it with:_
```html
<a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>{{translate('PASTRIES.LIST')}}</span></a>
```

Next we change the strings in our page template. We will reuse the `PASTRIES.LIST` key to replace `List of Pastries` and change the others as follows:

| Find                                          | Replace with           |
| --------------------------------------------- | ---------------------- |
| `Pastries`                                    | `PASTRIES`             |
| `This page provides a yummy list of pastries` | `PASTRIES.PAGE`        |
| `Name`                                        | `PASTRIES.NAME`        |
| `Origin`                                      | `PASTRIES.ORIGIN`      |
| `Description`                                 | `PASTRIES.DESCRIPTION` |

The page template should now look like this

**app/sprinkles/pastries/templates/pages/pastries.html.twig**:
```html
{% extends 'pages/abstract/dashboard.html.twig' %}

{# Overrides blocks in head of base template #}
{% block page_title %}{{translate('PASTRIES')}}{% endblock %}
{% block page_description %}{{translate('PASTRIES.PAGE')}}{% endblock %}

{% block body_matter %}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-cutlery fa-fw"></i> {{translate('PASTRIES.LIST')}}</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>{{translate('PASTRIES.NAME')}}</th>
                            <th>{{translate('PASTRIES.ORIGIN')}}</th>
                            <th>{{translate('PASTRIES.DESCRIPTION')}}</th>
                        </tr>
                        {% for pastry in pastries %}
                            <tr>
                                <td>{{pastry.name}}</td>
                                <td>{{pastry.origin}}</td>
                                <td>{{pastry.description}}</td>
                            </tr>
                        {% endfor %}
                    </table>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

Next we need to add the English mappings for those keys. We'll create a new `locale/en_US` directory, inside of which we'll create `pastries.php` to map our localization keys to their English translations:

**app/sprinkles/pastries/locale/en_US/pastries.php**:
```php
<?php

return [
    "PASTRIES" => [
        "@TRANSLATION"  => "Pastries",
        "PAGE"          => "This page provides a yummy list of pastries",
        "LIST"          => "List of Pastries",
        "NAME"          => "Name",
        "ORIGIN"        => "Origin",
        "DESCRIPTION"   => "Description"
    ]
];
```

Now it's time to create the French translation file. To avoid confusion, the French translation file should have the same name (`pastries.php`), but be placed in a `fr_FR` directory:

**app/sprinkles/pastries/locale/fr_FR/pastries.php**:
```php
<?php

return [
    "PASTRIES" => [
        "@TRANSLATION"  => "Pâtisseries",
        "PAGE"          => "Cette page propose une appétissante liste de pâtisseries",
        "LIST"          => "Liste des pâtisseries",
        "NAME"          => "Nom",
        "ORIGIN"        => "Origine",
        "DESCRIPTION"   => "Description"
    ]
];
```

At this point, you can visit the user preferences and change your language to French. Once this is done, go back to the pastries page and _voilà, tout en français_ !

![Pastries in French](/images/pastries/04.png)
