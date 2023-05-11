---
title: Twig Filters and Functions
metadata:
    description:
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

### checkAccess

You can perform permission checks in your Twig templates using the `checkAccess` helper function. This is useful when you want to render a portion of a page's content conditioned on whether or not a user has a certain permission. For example, this can be used to hide a navigation menu item for pages that the current user does not have access to:

```twig
{% if checkAccess('uri_users') %}
<li>
    <a href="{{site.uri.public}}/users"><i class="fa fa-user fa-fw"></i> {{ translate("USER", 2) }}</a>
</li>
{% endif %}
```

### translate

```twig
{{ translate("ACCOUNT_SPECIFY_USERNAME") }}

{{ translate("ACCOUNT_USER_CHAR_LIMIT", {min: 4, max: 200}) }}
```

### path_for

You can use `path_for` in your Twig templates to get the URL for a named route. This Twig function is simply mapped to the Slim router `pathFor(string $name, array $data, array $queryParams)` instance method.

```html
<li>
    <a href="{{ path_for('awesome-owls' )}}">Owls</a>
</li>
```

To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).
