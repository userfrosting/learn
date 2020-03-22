---
title: Adding the page to the menu
metadata:
    description: Adding our custom page to the sidebar menu using a template extension.
taxonomy:
    category: docs
---

Now that we have our page, it's time to add it to the sidebar menu. To do so, we will [extend the default menu template](/recipes/extending-template#adding-custom-menu-entries).

Create a new template file located in `templates/navigation/` and call it `sidebar-menu.html.twig`, so that it can replace the original sidebar menu template file:

**app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig**:
```twig
{% extends '@admin/navigation/sidebar-menu.html.twig' %}

{% block navigation %}
    {{ parent() }}
    <li>
        <a href="{{site.uri.public}}/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>List of Pastries</span></a>
    </li>
{% endblock %}
```

The key here is the `{% extends '@admin/navigation/sidebar-menu.html.twig' %}` part. While our new template overrides the version in the `admin` sprinkle, we tell **our** file to use the template from the admin sprinkle as a base. We can then use Twig inheritance to add our new link to the navigation block. See the [Extending Templates and Menus](/recipes/extending-template) recipe for more information about this.

You should now see the new link in the menu:

![Pastries menu link](/images/pastries/03.png)
