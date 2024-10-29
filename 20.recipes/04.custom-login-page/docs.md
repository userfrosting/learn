---
title: Customizing the login page
metadata:
    description: An example of how to modify UserFrosting's default login page and behavior.
taxonomy:
    category: docs
---

[notice=note]This recipe assumes that you've already setup your own UserFrosting instance from the Skeleton template and you're familiar with the basics of UserFrosting.[/notice]

[notice=tip]A complete example of this guide can be found on GitHub : [https://github.com/userfrosting/recipe-custom-login](https://github.com/userfrosting/recipe-custom-login)[/notice]

This recipe will guide you in customizing the UserFrosting login screen. Specifically, we'll explain how to:
- Disable the registration
- Change the destination the user is taken to after a successful login
- Changing the visual style of the login page

If you haven't already, set up your site Sprinkle as per the installation instructions. For the purposes of this tutorial, we will use the same info as the Skeleton : call our Sprinkle recipe `MyApp` with `UserFrosting\App` as a base namespace.

[notice]This recipe was originally sponsored by [adm.ninja](https://adm.ninja). [Get in touch with the UserFrosting team](https://chat.userfrosting.com) if you want to sponsor a custom recipe for your organization![/notice]

## Disabling registration

For many reasons, you may want to disable the ability for someone to create a new account. Fortunately, UserFrosting provides an option inside the [configuration files](/configuration/config-files) to disable the registration feature. Since you should never modify code directly in the core UserFrosting codebase, the clean way to do this is to **override the default configuration in your own Sprinkle**.

To do this, first we'll need to create a `app/config/` directory inside your Sprinkle directory structure. Inside this directory, we'll create a PHP file named `default.php`.

[notice=tip]The name of the configuration file is important. The `default` config file will be automatically loaded when your Sprinkle is included by the system. See the [Environment Mode](/configuration/config-files#environment-modes) chapter if you want to edit a configuration value for another environment mode.[/notice]

Inside your newly created config file, you add any configuration options you want to overwrite or add. In this case, we want to set the `site.registration.enabled` option to `false`:

`app/config/default.php`
```php
<?php
    return [
        'site' => [
            'registration' => [
                'enabled' => false
            ]
        ]
    ];
```

Save the file, reload the login page and voilà! Not only will the registration link disappear, but all relevant registration endpoints will also be deactivated. You will still be able to create a new user manually using the administration interface.

![Login form without registration](/images/login-no-registration.png)

See the [Configuration Files](/configuration/config-files) chapter for more information about editing configuration.

[notice=tip]If you need to be able to control basic configuration through the web interface, check out the `ConfigManager` Sprinkle from the [Community Sprinkles](https://github.com/search?q=topic%3Auserfrosting-sprinkle&type=Repositories). This easy to install Sprinkle provides a graphical UI to manage some of the basic UserFrosting settings, including registration, and provides APIs to add you own custom settings.[/notice]

## Changing the post-login destination

When a successful login occurs, by default, the user will be taken to the `/dashboard` page if it has access to this page, or `/settings` otherwise. 

To understand how this redirect works, when the user is logging in, the `UserRedirectedAfterLoginEvent` event will be dispatched. The **Admin Sprinkle** listen to this event, through the `UserRedirectedToSettings` and `UserRedirectedToDashboard` listeners : 

`vendor/userfrosting/sprinkle-admin/app/src/Admin.php`
```php
/**
 * N.B.: Last listeners will be executed first.
 */
public function getEventListeners(): array
{
    return [
        UserRedirectedAfterLoginEvent::class => [
            UserRedirectedToSettings::class,
            UserRedirectedToDashboard::class,
        ],
    ];
}
```

You'll notice two listeners are listening to the event. First `UserRedirectedToDashboard` will be executed and if the user has access to the Dashboard, the redirect will be set to `/dashboard`. If the user doesn't have access (via the authenticator checkAccess method), `UserRedirectedToSettings` will be called. 

This process can be easily customized by adding our own event listener. Let's change the default behavior to redirect every user to the about page (`/about` route) upon login.

First, create a `UserRedirectedToAbout` class in your Sprinkle with the following content:

`app/src/Listener/UserRedirectedToAbout.php`
```php
<?php

namespace UserFrosting\App\Listener;

use Psr\EventDispatcher\StoppableEventInterface;
use UserFrosting\Sprinkle\Core\Event\Contract\RedirectingEventInterface;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;

/**
 * Set redirect to index.
 */
class UserRedirectedToAbout
{
    public function __construct(
        protected RouteParserInterface $routeParser,
    ) {
    }

    /**
     * @param RedirectingEventInterface&StoppableEventInterface $event
     */
    public function __invoke($event): void
    {
        $path = $this->routeParser->urlFor('about');
        $event->setRedirect($path);
        $event->isPropagationStopped();
    }
}
```

[notice=note]Note that we use Slim's route parser `urlFor` method to get the route definition from it's name. This is the same as hardcoding `'/about'`. Check out [Slim's documentation](https://www.slimframework.com/docs/objects/router.html#route-names) for more info on named routes.[/notice]

The last step is to register the new listener in your Sprinkle Recipe. The recipe itself will also need to implement the `UserFrosting\Event\EventListenerRecipe` interface.

`app/src/MyApp.php`
```php
namespace UserFrosting\App;

use UserFrosting\App\Bakery\HelloCommand;
use UserFrosting\App\Listener\UserRedirectedToAbout; // <-- Add this
use UserFrosting\Event\EventListenerRecipe; // <-- Add this
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLoginEvent; // <-- Add this
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\BakeryRecipe;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

class MyApp implements
    SprinkleRecipe,
    BakeryRecipe
    EventListenerRecipe, // <-- Add this
{
    // ...

    public function getEventListeners(): array
    {
        return [
            UserRedirectedAfterLoginEvent::class => [
                UserRedirectedToAbout::class,
            ],
        ];
    }
    
    // ...
}
```

Since the last listener registered is called first, your sprinkle's listener will be called first. Plus, since your listener calls `$event->isPropagationStopped();`, the other listeners won't be called.

From now on, when a user logs in, they will be taken to the index page (`/` route). From there, you can change the redirect value to any route you want. You can also inject other services, like [authorizer](/users/access-control) in the default behavior, to add more logic to your redirect strategy.

## Custom style

Customizing the visual style of the login page is similar to any other component of UserFrosting. You might have guessed it - it involves **overwriting the default code in your own sprinkle**. To change the style of the login page, we'll need to override two types of resources: templates and assets. Note that this process is typically the same for any page you want to change within UserFrosting.

### Customizing the template

Now we will modify the login page to show the site logo. The base base template for this page is defined in `vendor/userfrosting/theme-adminlte/app/templates/pages/sign-in.html.twig`. However, we can't directly edit this file. We could copy the whole file from the AdminLTE theme, however what happens if this file changes in a future version? Our custom version would need to be updated as well. To minimize this, we will instead use the [extending templates](https://learn.userfrosting.com/recipes/extending-template) feature seen on the previous page. Create a new template file with the same name (`sign-in.html.twig`) and at the same location as the original one (`app/templates/pages`) and copy the next block.

`app/templates/pages/sign-in.html.twig`
```html
{% extends '@adminlte-theme/pages/sign-in.html.twig' %}

{% block loginLogo %}{% endblock %}

{% block loginBox %}
<div class="login-box">
    <div class="login-box-body login-form">
        <div class="login-logo">
            <a href="{{site.uri.public}}">
                <img src="{{ asset('assets/images/cupcake.svg') }}" style="width: 200px">
                <p>{{site.title}}</p>
            </a>
        </div>
        <!-- /.login-logo -->

        <div class="form-alerts" id="alerts-page"></div>

        {% block loginForm %}
            {{ parent() }}
        {% endblock %}

        {% block loginLinks %}
            {{ parent() }}
        {% endblock %}
    </div>
    <!-- /.login-box-body -->
</div>
{% endblock %}
```

A couple of elements to point out here :
1. We start by using the `extend` tag with the reference to `@adminlte-theme` template. This tells Twig to use the original template as base.
2. We add the `loginLogo`, but leave it empty to remove the default logo/title.
3. We define a custom `loginBox` block with our modified code. The `login-logo` div is now inside the `login-box-body` and the image is added. 
4. Since we still want to use both `loginForm` and `loginLinks` blocks, but don't want to change their content, we include them and use `{{ parent() }}` to render the content from the `@adminlte-theme` version

The last missing piece is the image we used isn't loaded in your app right now. This image is located in the AdminLTE theme sprinkle and must be copied to the `public/`. Fortunately, AdminLTE sprinkle provides an helper module to do this with Webpack. Simply add the following line to your `app/assets/app.js` and run Webpack.

`app/assets/app.js`
```
require('@userfrosting/theme-adminlte/app/assets/cupcake');
```

Run : 
```
$ php bakery assets:build
```

Once you refresh the page, you should see the result:

![Custom login template](/images/custom-login.png)

### Customizing the CSS

Customizing the CSS is different than overriding the template. Our change will be small, so we won't be replacing a full css file, but add a new, smaller one. Two steps are required to achieve this : 

1. Create a new css file
2. Register a new webpack entry

First, let's create a new `app/assets/css/sign-in.css` file and add the following code to that file. This will invert the colors of the background and login box on the login page. Feel free to make whatever styling changes you want on that page here.

**app/assets/css/sign-in.css**
```css
.login-page {
    background-color: #ffffff;
}

.login-box-body {
    background-color: #d2d6de;
}
```

Next, we need to create a webpack entry for the new css file. We'll assign it to the `css.sign-in` entry. In your Sprinkle's `/webpack.entries.js`, add this entry:

**webpack.entries.js**
```js
module.exports = {
    // ...
    'css.sign-in': './app/assets/css/sign-in.css',
    // ...
}
```

Next, we add the entry to the login page. At the bottom of the file, outside any of the block, add the `stylesheets_page` block:

**app/templates/pages/sign-in.html.twig**
```html
{% block stylesheets_page %}
    {{ encore_entry_link_tags('css.sign-in') }}
{% endblock %}
```

Last, we need to rebuild the assets, by running the bakery command : 

```bash
php bakery assets:build
```

Your new CSS file should be loaded when you refresh the page and you should see the result:

![Custom login style](/images/custom-login2.png)
