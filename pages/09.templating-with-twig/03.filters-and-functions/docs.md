---
title: Twig Filters and Functions
metadata:
    description: 
taxonomy:
    category: docs
---

### checkAccess

You can perform permission checks in your Twig templates using the `checkAccess` helper function.  This is useful when you want to render a portion of a page's content conditioned on whether or not a user has a certain permission.  For example, this can be used to hide a navigation menu item for pages that the current user does not have access to:

```twig
{% if checkAccess('uri_users') %}
<li>
    <a href="{{site.uri.public}}/admin/users"><i class="fa fa-user fa-fw"></i> {{ translate("USER", 2) }}</a>
</li>
{% endif %}
```