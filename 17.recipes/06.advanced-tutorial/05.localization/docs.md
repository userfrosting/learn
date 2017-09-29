---
title: Adding localizations
metadata:
    description: Adding localization to the page, making the page available in multiples languages.
taxonomy:
    category: docs
---

While we are using our non root user, it's the perfect opportunity to test localization for our page. We will first need to change the hardcoded English string to localization keys and afterwards we'll add the French translations. 

Even if you don't need (or want) to translate your page, it's a good idea to use translations keys and the translator when you first build your pages. You never know when or why you'll need to add another language in the future and it's harder to add localization later than do it while you're building your page, even if it takes a little more time.

First thing is to switch the hard-coded string for the English translation. At this point there’s a couple of templates and place to change: The page title, the sidebar menu entry, the box title and the table header.

Let's start with the sidebar menu. Here we change the `List of Pastries` to `{{translate('PASTRIES.LIST')}}` (We'll add the `PASTRIES.LIST` locale key later).


`app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig`
```html
Find:
<a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>List of Pastries</span></a>

Replace it by: 
<a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>{{translate('PASTRIES.LIST')}}</span></a>
```

Next we change the strings from our page template. We will reuse `PASTRIES.LIST` key to replace `List of Pastries` and change the other like this:
- `Pastries` => `PASTRIES`
- `This page provides a yummy list of pastries` => `PASTRIES.PAGE`
- `Name` => `PASTRIES.NAME`
- `Origin` => `PASTRIES.ORIGIN`
- `Description` => `PASTRIES.DESCRIPTION`

The page template should now look like this
`app/sprinkles/pastries/templates/pages/pastries.html.twig`
```html
{% extends "pages/abstract/dashboard.html.twig" %}

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

Next we need to add the English association for those keys. We now create a new `locale/en_US` directory and a `pastries.php` file to old our new translations:

`app/sprinkles/pastries/locale/en_US/pastries.php`
```php
<?php

return [
    "PASTRIES" => [
        "@TRANSLATION" => "Pastries",
        "PAGE" => "This page provides a yummy list of pastries",
        "LIST" => "List of Pastries",
        "NAME" => "Name",
        "ORIGIN" => "Origin",
        "DESCRIPTION" => "Description"
    ]
];
```

Now it's time to create the French translation file. To avoid confusion, the French translation should be on the same file (`pastries.php`), but place it in a `fr_FR` directory:

`app/sprinkles/pastries/locale/fr_FR/pastries.php`
```php
<?php

return [
    "PASTRIES" => [
        "@TRANSLATION" => "Pâtisseries",
        "PAGE" => "Cette page propose une appétissante liste de pâtisseries",
        "LIST" => "Liste des pâtisseries",
        "NAME" => "Nom",
        "ORIGIN" => "Origine",
        "DESCRIPTION" => "Description"
    ]
];
```

At this point, you can go to your user preferences and change the site language to French. Once this is done, go back to the pastries page and _voilà, tout en français_ !

![Pastries in French](/images/pastries/04.png)