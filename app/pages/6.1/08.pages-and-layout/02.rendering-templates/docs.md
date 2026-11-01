---
title: Rendering Templates
description: Learn how to render Twig templates from your controllers and return HTML responses to users.
---

In your app, controllers act as the bridge between your data and your presentation layer (templates). They handle requests, fetch or process data, and pass it to templates for rendering. This page shows you the essential techniques for displaying templates to users — from basic rendering to handling complex data and error cases.

UserFrosting provides the `view` service (an instance of Twig's `Environment`) for rendering templates. You'll typically access this in your controllers through dependency injection.

## Basic Template Rendering

The most common pattern is rendering a template from a controller action:

**Controller (PHP):**
```php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PageController
{
    public function __construct(
        protected Twig $view
    ) {
    }

    public function displayPage(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'pages/my-page.html.twig', []);
    }
}
```

**Template (`templates/pages/my-page.html.twig`):**
```twig
{% extends "layouts/default.html.twig" %}

{% block content %}
    <h1>My Page Title</h1>
    <p>Welcome back, user!</p>
{% endblock %}
```

**Breaking this down:**

1. **Inject the `Twig` service** - Use dependency injection to get access to Twig
2. **Call `render()`** - Pass the response object, template path, and data array
3. **Template path** - Relative to your sprinkle's `templates/` directory
4. **Data array** - Variables you want to use in the template (empty array for now)
5. **Return the response** - The `render()` method writes to the response and returns it

## Passing Data to Templates

The third parameter to `render()` is an associative array where keys become variable names in your template.

**Controller (PHP):**
```php
return $this->view->render($response, 'pages/dashboard.html.twig', [
    'pageTitle' => 'Dashboard',
    'user' => $currentUser,
    'stats' => [
        'total_users' => 150,
        'active_sessions' => 23,
    ],
    'notifications' => $notifications,
]);
```

**Template (`templates/pages/dashboard.html.twig`):**
```twig
{% extends "layouts/default.html.twig" %}

{% block content %}
    <h1>{{ pageTitle }}</h1>
    <p>Welcome, {{ user.first_name }}!</p>
    
    <div class="stats">
        <div>Total Users: {{ stats.total_users }}</div>
        <div>Active Sessions: {{ stats.active_sessions }}</div>
    </div>

    <div class="notifications">
        {% for notification in notifications %}
            <div class="alert">{{ notification.message }}</div>
        {% endfor %}
    </div>
{% endblock %}
```

### Rendering with Database Results

Query results can be directly passed to templates.

**Controller (PHP):**
```php
use App\Database\Models\Post;

public function listPosts(Request $request, Response $response): Response
{
    $posts = Post::where('published', true)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    return $this->view->render($response, 'pages/posts.html.twig', [
        'posts' => $posts,
    ]);
}
```

**Template (`templates/pages/posts.html.twig`):**
```twig
{% extends "layouts/default.html.twig" %}

{% block content %}
    <h1>Recent Posts</h1>
    
    {% if posts is empty %}
        <p>No posts found.</p>
    {% else %}
        <div class="posts-list">
            {% for post in posts %}
                <article class="post">
                    <h2><a href="/posts/{{ post.slug }}">{{ post.title }}</a></h2>
                    <p class="meta">
                        By {{ post.author.name }} on {{ post.created_at|date('F j, Y') }}
                    </p>
                    <p>{{ post.excerpt }}</p>
                </article>
            {% endfor %}
        </div>
    {% endif %}
{% endblock %}
```
