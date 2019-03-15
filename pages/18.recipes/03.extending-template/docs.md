---
title: Extending templates and menus
metadata:
    description: Steps to extend a template defined in another sprinkle.
taxonomy:
    category: docs
---

>>> This recipe assumes that the reader is familiar with [Twig templating](/templating-with-twig).

While sprinkles are able to [overwrite an entire Twig template](/templating-with-twig/sprinkle-templates#overriding-sprinkle-templates) defined in another sprinkle, they can also replace part of a template instead of overwriting everything in them.

This is achieved by referencing another sprinkle directly in the `extends` Twig tag. Typically, page template files will extend a base template. For example:

```twig
{% extends "pages/abstract/dashboard.html.twig" %}
```

You can instead extend the same file you're in by adding the sprinkle name in front of the name. For example, for the `pages/user.html.twig` file in the `admin` sprinkle, you can do :

```twig
{% extends "@admin/pages/user.html.twig" %}
```

This will allow you to replace any Twig `block` definition contained from the `admin` version of the `pages/user.html.twig` template.

## Adding custom menu entries

Adding new link to the built-in menus can be done using the technique above. For example, to add a link to the sidebar menu, all you have to do is create a `navigation/sidebar-menu.html.twig` template in your sprinkle and extending the admin sprinkle version of the same file. You can then add content to the `navigation` block :

```twig
{% extends '@admin/navigation/sidebar-menu.html.twig' %}

{% block navigation %}
    
    {{ parent() }}
    
    <li>
        <a href="/moon"><i class="fa fa-rocket fa-fw"></i> <span>To the moon !</span></a>
    </li>
{% endblock %}
``` 

This will add a `To the moon!` link under the built in links in the dashboard layout sidebar menu. 

The `{{ parent() }}` tag will load the content defined in the `navigation` of the parent template. The above example will add the custom link to the bottom of the list. To add the new link to the top of the list, simply put the parent tag under your own link. Unfortunately, there's no way at the moment to add a link in the middle of the other links.

>>>>>> When using multiple sprinkles, each one might want to add content to the menus. Since they probably each reference the `admin` sprinkle as the base, only the top sprinkle will have the privilege to add links to the sidebar menu. Since the top sprinkle is usually the one tying your site together, it is expected that it should be in charge of assembling the final menu.
