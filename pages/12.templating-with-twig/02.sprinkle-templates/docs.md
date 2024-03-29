---
title: Sprinkle Templates
metadata:
    description:
taxonomy:
    category: docs
---

## Template organization

Any Sprinkle that works with Twig templates will contain a `templates/` directory. By convention, this directory is structured as follows:

```txt
templates/
├── content/
├── forms/
├── mail/
├── modals/
├── navigation/
├── pages/
└── tables/
```

By replicating this structure in your own Sprinkle, it is possible to completely override a core template with your own. When a template is rendered, Twig will search for the specified path relative to the most recently loaded Sprinkle, falling back to previously loaded Sprinkles until it finds a match.

### Page templates

The `pages/` directory contains templates that correspond to specific pages in your application. For example, the main content template for `http://owlfancy.com/supplies/preening` might be located at `pages/supplies/preening.html.twig`.

### Abstract templates

One particularly powerful feature of Twig is the ability to **extend** templates. The concept is similar to class inheritance in object-oriented programming. We can define a **base template**, and then override parts of the base template in a **child template**. Thus, we can have many child templates that inherit from the same base template. If we want to modify some common feature in all of those pages, all we need to do is modify the base template.

UserFrosting comes with a set of "abstract" templates - templates that are not meant to be used directly as pages, tables, modals, etc., but rather to be extended by other templates.

For example, The base template `pages/abstract/base.html.twig`, found in the `core` Sprinkle, serves as an abstract page template from which all other pages ultimately derive. The main purpose of this template is to define the basic structure of an HTML page. Let's take a look:

```twig
{# base.html.twig: This is the base abstract template for all pages. #}

{% block page %}
<!DOCTYPE html>
<html lang="en-US">
    {% block head %}
        <head>
            <!-- The 'X-UA-Compatible' tag must be first, otherwise IE will not respect it.  See http://stackoverflow.com/questions/25557299/internet-explorer-11-disable-display-intranet-sites-in-compatibility-view-via -->
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta charset="utf-8">
            <meta name="generator" content="UserFrosting" />
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
            <meta name="description" content="{% block page_description %}This page has not yet been configured.{% endblock %}">
            <meta name="author" content="{% block page_author %}{{ site.author }}{% endblock %}">

            <title>{{ site.title }} | {% block page_title %}New Page{% endblock %}</title>

            {% include "pages/partials/favicons.html.twig" %}

            {# Use this block to add extra content in page head without having to override the entire base layout #}
            {% block head_extra %}{% endblock %}

            {% block stylesheets %}
                {# Override this block in a child abstract template or page template to override site-level stylesheets. #}
                {% block stylesheets_site %}
                    <!-- Include main CSS asset bundle -->
                    {{ assets.css() | raw }}
                {% endblock %}

                {# Override this block in a child abstract template or page template to specify or override stylesheets for groups of similar pages. #}
                {% block stylesheets_page_group %}
                {% endblock %}

                {# Override this block in a child abstract template or page template to specify or override page-level stylesheets. #}
                {% block stylesheets_page %}
                {% endblock %}
            {% endblock %}

            {# Site author link #}
            {% if site.uri.author %}
                <link href="{% block page_author_link %}{{ site.uri.author }}{% endblock %}" rel="author" />
            {% endif %}

            {# Site publisher link #}
            {% if site.uri.publisher %}
                <link href="{% block page_publisher_link %}{{ site.uri.publisher }}{% endblock %}" rel="publisher" />
            {% endif %}

            {# Canonical page link #}
            {% if block('page_canonical') %}
                <link href="{{site.uri.base.scheme}}://{% block page_canonical %}{% endblock %}" rel="canonical" />
            {% endif %}

            <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
            <!--[if lt IE 9]>
                <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->

            {% include "pages/partials/analytics.html.twig" %}
        </head>
    {% endblock %}

    {% block body %}
        <body {% block body_attributes %}{% endblock %}>
            <!-- Page Content -->
            {% block content %}{% endblock %}

            <!-- Javascript configuration -->
            <script>
            {% include "pages/partials/config.js.twig" %}
            </script>

            {% block scripts %}
                {# Override this block in a child abstract template or page template to override site-level scripts. #}
                {% block scripts_site %}
                    <!-- Load jQuery -->
                    <script src="//code.jquery.com/jquery-2.2.4.min.js" ></script>
                    <!-- Fallback if CDN is unavailable -->
                    <script>window.jQuery || document.write('<script src="{{ assets.url('assets://vendor/jquery/dist/jquery.min.js', true) }}"><\/script>')</script>

                    {{ assets.js() | raw }}
                {% endblock %}

                {# Override this block in a child abstract template or page template to specify or override scripts for groups of similar pages. #}
                {% block scripts_page_group %}
                {% endblock %}

                {# Override this block in a child abstract template or page template to specify or override page-level scripts. #}
                {% block scripts_page %}
                {% endblock %}

                {# Override this block to alter UfAlerts. #}
                {% block scripts_UFAlert %}
                    <script>
                        $("#alerts-main").ufAlerts();
                        $("#alerts-main").ufAlerts('fetch').ufAlerts('render');
                    </script>
                {% endblock %}
            {% endblock %}

        </body>
    {% endblock %}
</html>
{% endblock %}
```

You'll notice that this template is composed almost entirely of `block` blocks. The [`block` tag](https://twig.symfony.com/doc/3.x/tags/extends.html) allows us to define named blocks of code that can be overridden in our child templates. In many cases, these blocks are completely empty in our base template, and serve mainly as a way to define placeholders that we can fill in with content in our child templates.

To define a child template, we use the [`extends` tag](https://twig.symfony.com/doc/3.x/tags/extends.html) at the top of a new file, and then define the blocks we wish to override:

```twig
{# This is a child template, which inherits from base.html.twig. #}

{% extends "pages/abstract/base.html.twig" %}

{% block content %}
    Hello World!
{% endblock %}
```

When we render this template, Twig will use `base.html.twig`, but then replace any instances of the `content` block with the contents of the `content` block we've defined in the child template.

[notice=info]Like with OOP, child templates can themselves be extended, creating a hierarchy of template inheritance. For example, in the `core` sprinkle, `pages/abstract/default.html.twig` extends `pages/abstract/base.html.twig`, and `pages/index.html.twig` extends `pages/abstract/default.html.twig`.[/notice]

### Partial templates

Sometimes, we want to reuse a snippet across multiple different templates - for example, a footer or a message box that needs to appear in multiple different types of pages. We refer to these types of templates as **partial templates**. Partial templates can be included in another template via Twig's [`include` tag](https://twig.symfony.com/doc/3.x/tags/include.html). Suppose, for example, we have a page template:

```twig
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta name="generator" content="UserFrosting" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="description" content="{% block page_description %}This page has not yet been configured.{% endblock %}">
        <meta name="author" content="{% block page_author %}{{ site.author }}{% endblock %}">

        <title>{{ site.title }} | {% block page_title %}New Page{% endblock %}</title>

        {% include "pages/partials/favicons.html.twig" %}

        ...
```

The last line `{% include "pages/partials/favicons.html.twig" %}` tells Twig to insert the contents of the `pages/partials/favicons.html.twig` template.  Additional parameters can be passed to `include`, which will override any variables of the same name that were passed to the main template:

```twig
<div class="box-body">
    {% include "tables/users.html.twig" with {
            "table" : {
                "id" : "table-users",
                "columns" : ["last_activity"]
            }
        }
    %}
</div>
```

This sets custom values for the `table` variable used in `tables/users.html.twig`.

Templates in the following directories are all "partial" templates:

```txt
templates/
├── content/
├── forms/
├── navigation/
├── pages/
│   └── partials/
└── tables/
```

### Mail templates

`mail/` contains email templates - see [Mail](/mail) for more information.

## Overriding Sprinkle templates

To completely override a template in a Sprinkle, simply redefine it with the same name and relative path in your Sprinkle:

`/app/sprinkles/core/templates/pages/about.html.twig`:

```twig

{% extends "pages/abstract/default.html.twig" %}

{% set page_active = "about" %}

{# Overrides blocks in head of base template #}
{% block page_title %}About{% endblock %}

{% block page_description %}All about my UserFrosting website.{% endblock %}

{% block body_matter %}
    <!-- Page Heading/Breadcrumbs -->
    <div class="row">

    ...

{% endblock %}
```

`/app/sprinkles/site/templates/pages/about.html.twig`:

```twig

{% extends "pages/abstract/default.html.twig" %}

{% set page_active = "about" %}

{# Overrides blocks in head of base template #}
{% block page_title %}About OwlFancy.com{% endblock %}

{% block page_description %}OwlFancy.com - history, facts, and fiction.{% endblock %}

{% block body_matter %}
    Owl Fancy was founded in 1943 in response to an owl shortage both domestically and abroad. Civilians across the globe were asked to contribute their owls towards the war effort, resulting in large-scale deowlment throughout the countryside. Exploding vole populations were...

{% endblock %}
```

Then, if we had the following code in a controller:

```PHP
return $this->ci->view->render($response, 'pages/about.html.twig');
```

Twig would resolve to the `pages/about.html.twig` file in the `site` Sprinkle's `template` directory, since `site` is loaded after `core`.
