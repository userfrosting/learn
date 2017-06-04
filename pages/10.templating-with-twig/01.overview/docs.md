---
title: Why use Twig?
metadata:
    description: UserFrosting uses the extremely popular Twig templating engine to facilitate clean separation between content and logic in your application.
taxonomy:
    category: docs
---

Twig is a [templating engine](http://twig.sensiolabs.org/), which is designed to help maintain a clean separation between your application's logic and its content.

If you recall the spaghetti code example from Chapter 8:

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
