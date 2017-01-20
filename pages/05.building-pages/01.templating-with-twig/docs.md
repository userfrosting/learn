---
title: Templating with Twig
metadata:
    description: UserFrosting uses the extremely popular Twig templating engine to facilitate clean separation between content and logic in your application.
taxonomy:
    category: docs
---

Twig is a [templating engine](http://twig.sensiolabs.org/), which is designed to help maintain a clean separation between your application's logic and its content.

If we go back to the spaghetti code example:

```php
if (isset($_POST)) {
    $stmt = $db->prepare("INSERT INTO users (:username, :email)");
    $stmt->execute([
        ':username' => $_POST['username'],
        ':email' => $_POST['email']
    ]);
} else {
    echo "<table><tr><th>Username</th><th>Email</th></tr>";

    $stmt = $db->prepare("SELECT * FROM users");
    $stmt->execute();

    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>$r['user_name']</td><td>$r['email']</td></tr>";
    }

    echo "</table>"
}
```

You'll notice that the HTML we generate is heavily intermingled with PHP code.  Wouldn't it be easier to read and maintain if we could separate the two?  If we used Twig, we could factor all of our HTML out into a **template** file:

**user-table.html.twig**

```html
<table>
    <tr>
        <th>Username</th>
        <th>Email</th>
    </tr>

    {% for user in users %}
        <tr>
            <td>{{user.user_name}}</td>
            <td>{{user.email}}</td>
        </tr>
    {% endfor %}
</table>
```

Notice the `{{ mustache }}` syntax, which tells Twig where to substitute dynamic content into the template.  Our PHP code can now **render** this template, passing in any required dynamic content:

```php
if (isset($_POST)) {
    $stmt = $db->prepare("INSERT INTO users (:username, :email)");
    $stmt->execute([
        ':username' => $_POST['username'],
        ':email' => $_POST['email']
    ]);
} else {
    $stmt = $db->prepare("SELECT * FROM users");
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Assume that $twig is an instance of the Twig view object
    echo $twig->render('user-table.html.twig', [
        'users' => $users
    ]);
}
```

So, what did this get us?  Well in this example, not _that_ much.  We don't have our HTML littered with PHP syntax anymore, like `echo` and `;`, though we still need some logic in our template to loop through the array of `users` and render each row in our table.  We could conceivably pass off our Twig template to our web designer friend, who knows HTML and CSS but isn't familiar with PHP, and he could get to work styling the page.

Our PHP code also looks better, because it's no longer full of calls to `echo` and mixed with scraps of HTML.  Overall, it's a solid improvement.  The _real_ power of Twig, however, comes from its more advanced features:

- Using `include` and `extend`, we can reuse HTML components like headers and footers on multiple pages, and develop "child pages" that build off a common base template;
- Twig, unless directed otherwise, will automatically escape dynamic content.  This protects your pages from [XSS vulnerabilities](https://www.owasp.org/index.php/Cross-site_Scripting_(XSS)).
- Template files can be completely overridden, so you can modify page content in your Sprinkle without touching the UserFrosting core.

We'll explain these features more as we discuss how Twig is used in UserFrosting.

## Sprinkle templates

Any Sprinkle that works with Twig templates will contain a `templates/` directory.  By convention, this directory is structured as follows:

```
templates/
├── components/
├── layouts/
├── mail/
└── pages/
```

By replicating this structure in your own Sprinkle, it is possible to completely override a core template with your own.  When a template is rendered, Twig will search for the specified path relative to the most recently loaded Sprinkle, falling back to previously loaded Sprinkles until it finds a match.

### components/

The `components/` directory contains partial HTML and Javascript templates, such as forms, tables, navigation bars, and other commonly reused components.

Templates in `components/` are typically used via Twig's [`include` tag](http://twig.sensiolabs.org/doc/2.x/tags/include.html).  Suppose, for example, we have a page template:

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

        {% include "components/favicons.html.twig" %}

        ...
```

The last line `{% include "components/favicons.html.twig" %}` tells Twig to insert the contents of the `components/favicons.html.twig` template.  Additional parameters can be passed to `include`, which will override any variables of the same name that were passed to the main template:

```twig
<div class="box-body">
    {% include "components/tables/users.html.twig" with {
            "table" : {
                "id" : "table-users",
                "columns" : ["last_activity"]
            }
        }
    %}
</div>
```

This sets custom values for the `table` variable used in `components/tables/users.html.twig`.

In some Sprinkles, the `components/` directory is further divided into directories containing common types of components.  For example, the `admin/` Sprinkle contains subdirectories for `forms/`, `modals/`, and `tables/`.

### layouts/

One particularly powerful feature of Twig is the ability to **extend** templates.  The concept is similar to class inheritance in object-oriented programming.  We can define a **base template**, and then override parts of the base template in a **child template**.  Thus, we can have many child templates that inherit from the same base template.  If we want to modify some common feature in all of those pages, all we need to do is modify the base template.

UserFrosting's base templates are generally kept in the `layouts/` directory.  Every UserFrosting page inherits from the base layout template `layouts/basic.html.twig`, found in the `core` Sprinkle.  The main purpose of this template is to define the basic structure of an HTML page.  Let's take a look:

```twig
{# basic.html.twig: This is the base layout template for all pages.  #}

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

            {% include "components/favicons.html.twig" %}

            {# Use this block to add extra content in page head without having to override the entire base layout #}
            {% block head_extra %}{% endblock %}

            {% block stylesheets %}
                {# Override this block in a child layout template or page template to override site-level stylesheets. #}
                {% block stylesheets_site %}
                    <!-- Include main CSS asset bundle -->
                    {{ assets.css() | raw }}
                {% endblock %}

                {# Override this block in a child layout template or page template to specify or override stylesheets for groups of similar pages. #}
                {% block stylesheets_page_group %}
                {% endblock %}

                {# Override this block in a child layout template or page template to specify or override page-level stylesheets. #}
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

            {% include "components/analytics.html.twig" %}
        </head>
    {% endblock %}

    {% block body %}
        <body {% block body_attributes %}{% endblock %}>
            <!-- Page Content -->
            {% block content %}{% endblock %}

            <!-- Javascript configuration -->
            <script>
            {% include "components/config.js.twig" %}
            </script>

            {% block scripts %}
                {# Override this block in a child layout template or page template to override site-level scripts. #}
                {% block scripts_site %}
                    <!-- Load jQuery -->
                    <script src="//code.jquery.com/jquery-2.2.4.min.js" ></script>
                    <!-- Fallback if CDN is unavailable -->
                    <script>window.jQuery || document.write('<script src="{{ assets.url('assets://vendor/jquery/dist/jquery.min.js', true) }}"><\/script>')</script>

                    {{ assets.js() | raw }}
                {% endblock %}

                {# Override this block in a child layout template or page template to specify or override scripts for groups of similar pages. #}
                {% block scripts_page_group %}
                {% endblock %}

                {# Override this block in a child layout template or page template to specify or override page-level scripts. #}
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

You'll notice that this template is composed almost entirely of `block` blocks.  The [`block` tag](http://twig.sensiolabs.org/doc/2.x/tags/extends.html) allows us to define named blocks of code that can be overridden in our child templates.  In many cases, these blocks are completely empty in our base template, and serve mainly as a way to define placeholders that we can fill in with content in our child templates.

To define a child template, we use the [`extends` tag](http://twig.sensiolabs.org/doc/tags/extends.html) at the top of a new file, and then define the blocks we wish to override:

```twig
{# This is a child template, which inherits from basic.html.twig. #}

{% extends "layouts/basic.html.twig" %}

{% block content %}
    Hello World!
{% endblock %}
```

When we render this template, Twig will use `basic.html.twig`, but then replace any instances of the `content` block with the contents of the `content` block we've defined in the child template.

>>> Like with OOP, child templates can themselves be extended, creating a hierarchy of template inheritance.  For example, in the `core` sprinkle, `layouts/default.html.twig` extends `layouts/basic.html.twig`, and `pages/index.html.twig` extends `layouts/default.html.twig`.


### mail/

`mail/` contains email templates - see [Chapter 11](/other-services/mail) for more information.

### pages/

You can think of the `layouts/` directory as a place to put your "abstract" templates - templates that are meant to be extended by other templates rather than being used directly.  `pages/` on the other hand, contains the concrete templates that correspond to specific pages in your application.  For example, the main content template for `http://owlfancy.com/supplies/preening` might be located at `pages/supplies/preening.html.twig`.
