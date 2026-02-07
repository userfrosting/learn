---
title: Introduction
description: If you're new to object-oriented programming, you may not be familiar with the MVC pattern, a popular and very flexible design paradigm for scalable, easily maintained web applications.
---

Imagine building a web application where all your code lives in single files—database queries mixed with HTML output, business logic tangled with form processing, everything in one giant mess. This is **spaghetti code**, and it's a maintenance nightmare. Want to change the database? You're rewriting HTML. Need to update the UI? You're touching business logic. Testing becomes impossible.

The **Model-View-Controller (MVC)** pattern solves this by separating concerns into three distinct layers: the **model** (data and business logic), the **view** (presentation/HTML), and the **controller** (coordinates between model and view). Each layer has one job, making your code easier to understand, test, and maintain.

UserFrosting embraces MVC fully. Models use [Eloquent](/database) for database interactions, views use [Twig templates](/templating-with-twig) for clean HTML generation, and controllers (built on [Slim 4](https://www.slimframework.com/)) tie everything together. This chapter focuses on controllers—the starting point for adding new features to your application.

## Understanding Spaghetti Code

If you come from a "traditional" PHP background, you may be used to seeing code that looks like this:

**users.php**
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

This is what is commonly referred to as ***spaghetti code***. All of the logic and presentation for a feature is mixed up into a single file. There is little or no object-oriented design, and probably a lot of repetitive code from one feature to the next. It's also really difficult to write clean HTML when we're building it with `echo` statements and interpolating all sorts of PHP statements with the HTML content.

MVC organizes our application into three main domains - the **model**, which represents our [database](/database) entities and other types of encapsulated logic; the **view**, which generates the final output (often HTML) that the user receives; and the **controller**, which controls the flow of interaction between the model and the view (and may contain some logic of its own as well).

UserFrosting uses a templating engine called [Twig](/templating-with-twig) to handle the rendering of HTML output in the view. UserFrosting's model consists of a set of [Eloquent](https://laravel.com/docs/10.x/eloquent) models for handling interactions with the database, as well as a number of other accessory classes that perform most of the heavy lifting for your application. We'll talk about both of these in later chapters.

In this chapter, we discuss UserFrosting's **controller**, which is based around the [Slim 4](https://www.slimframework.com/docs/v4/) microframework. Whenever you are looking to add a new page or feature to your application, you probably want to start with the controller.
