---
title: Adding the page to the menu
metadata:
    description: Adding our custom page to the sidebar menu using template extension.
taxonomy:
    category: docs
---

Now that we have our page, it's time to add it to the sidebar menu. Do to so, we will [extend the default menu template](/recipes/extending-template#adding-custom-menu-entries). 

Create a new template file located in `templates/navigation/` and call it `sidebar-menu.html.twig` so it can replace the original sidebar menu template file:

`app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig`
```html
{% extends "@admin/navigation/sidebar-menu.html.twig" %}

{% block navigation %}
    {{ parent() }}
    <li>
        <a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>List of Pastries</span></a>
    </li>
{% endblock %}
``` 

The key here is the `{% extends "@admin/navigation/sidebar-menu.html.twig" %}` part. While our new file overwrite the same one from the `admin` sprinkle, we tell **our** file to use the one from the admin sprinkle as a base. We ccan then use Twig inheritance to add our new link to the navigation block. See the [Extending Templates and Menus](/recipes/extending-template) receipe for more informations about this.

You should now see the new link in the menu :

![Pastries menu link](/images/pastries/03.png)