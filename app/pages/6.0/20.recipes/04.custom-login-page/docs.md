---
title: Customizing the login page
metadata:
    description: An example of how to modify UserFrosting's default login page and behavior.
taxonomy:
    category: docs
---

[notice]This recipe assumes that you've already setup your own sprinkle and you're familiar with the basics of UserFrosting.[/notice]

This recipe will guide you in customizing the UserFrosting login screen. Specifically, we'll explain how to:
- Disable the registration
- Change the destination the user is taken to after a successful login
- Changing the visual style of the login page

If you haven't already, set up your site Sprinkle as per the instructions in ["Your First UserFrosting Site"](/sprinkles/first-site). For the purposes of this tutorial, we will call our Sprinkle `site` with `App\Site` as a base namespace..

[notice]This recipe was originally sponsored by [adm.ninja](https://adm.ninja). [Get in touch with the UserFrosting team](https://chat.userfrosting.com) if you want to sponsor a custom recipe for your organization![/notice]

## Disabling registration

For many reasons, you may want to disable the ability for someone to create a new account. Fortunately, UserFrosting provides an option inside the [configuration files](/configuration/config-files) to disable the registration feature. Since you should never modify code directly in the core UserFrosting codebase, the clean way to do this is to **override the default configuration in your own Sprinkle**.

To do this, first we'll need to create a `config/` directory inside your Sprinkle directory structure. Inside this directory, we'll create a PHP file named `default.php`.

[notice=tip]The name of the configuration file is important. The `default` config file will be automatically loaded when your Sprinkle is included by the system. See the [Environment Mode](/configuration/config-files#environment-modes) chapter if you want to edit a configuration value for another environment mode.[/notice]

Inside your newly created `config/default.php` file, you add any configuration options you want to overwrite or add. In this case, we want to set the `site.registration.enabled` option to `false`:

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

When a successful login occurs, by default, the user will be taken to the `/dashboard` page if it has access to this page, or `/settings` otherwise. To understand how this redirect works, when the user is logging in, the `UserRedirectedAfterLoginEvent` event will be dispatched. The Admin Sprinkle listen to this event, through the `UserRedirectedToSettings` and `UserRedirectedToDashboard` listeners : 

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

This process can be easily customized by adding our own event listener. Let's change the default behavior to redirect every user to the index page (`/` route) upon login.

First, create a class `src/Listener/UserRedirectedToIndex.php` in your Sprinkle with the following content:

```php
<?php

namespace App\Site\Listener;

use Psr\EventDispatcher\StoppableEventInterface;
use Slim\Interfaces\RouteParserInterface;
use UserFrosting\Sprinkle\Core\Event\Contract\RedirectingEventInterface;

/**
 * Set redirect to index.
 */
class UserRedirectedToIndex
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
        $path = $this->routeParser->urlFor('index');
        $event->setRedirect($path);
        $event->isPropagationStopped();
    }
}
```

[notice=note]Note that we use Slim's route parser `urlFor` method to get the route definition from it's name. This is the same as hardcoding `'/'`. Check out [Slim's documentation](https://www.slimframework.com/docs/objects/router.html#route-names) for more info on named routes.[/notice]

The last step is to register the new listener in your Sprinkle Recipe. The recipe itself will also need to implement the `UserFrosting\Event\EventListenerRecipe`.

```php
namespace App;

use App\Site\Listener\UserRedirectedToIndex; // <-- Add here !
use UserFrosting\Event\EventListenerRecipe; // <-- Add here !
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLoginEvent; // <-- Add here !
use UserFrosting\Sprinkle\SprinkleRecipe;

class Site implements 
    SprinkleRecipe, 
    EventListenerRecipe, // <-- Add here !
{
    // ...

    public function getEventListeners(): array
    {
        return [
            UserRedirectedAfterLoginEvent::class => [
                UserRedirectedToIndex::class,
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

Let's start by overriding the base template for the login page. First, copy the base template to your sprinkle. You'll find the base template in `app/sprinkles/account/templates/pages/sign-in.html.twig`. Once copied over to your sprinkle, any changes made to your copy will override the base version, as long as it follows the same folder structure.

For this example, let's change the site title position:

```html
<div class="login-box">
    <div class="login-box-body login-form">
        <div class="login-logo">
            <a href="{{site.uri.public}}">
                <img src="{{assets.url('assets://userfrosting/images/cupcake.png')}}">
                <p>{{site.title}}</p>
            </a>
        </div>
        <!-- /.login-logo -->

        <div class="form-alerts" id="alerts-page"></div>

        <form action="{{site.uri.public}}/account/login" id="sign-in" method="post">
          ...
        </form>

        <a href="{{site.uri.public}}/account/forgot-password">{{translate('PASSWORD.FORGET')}}</a><br>
        <a href="{{site.uri.public}}/account/resend-verification">{{translate('ACCOUNT.VERIFICATION.RESEND')}}</a><br>
        {% if site.registration.enabled %}
            <a href="{{site.uri.public}}/account/register">{{translate('REGISTER')}}</a>
        {% endif %}

    </div>
    <!-- /.login-box-body -->
</div>
```

Did you notice how the `login-logo` div is now inside the `login-box-body` and the image, from the *core* sprinkle, was added? Once you refresh the page, you should see the result:

![Custom login template](/images/custom-login.png)

[notice=tip]You can also use the blocks definition to partially edit a template. See the [Extending Templates and Menus](/recipes/extending-template) recipe for more information.[/notice]

### Customizing the CSS

Customizing the CSS is similar to overriding the template, except that it involves registering a new asset file in the asset-bundle definitions. Since UserFrosting uses the [AdminLTE](https://adminlte.io) theme, the default login page style comes directly from AdminLTE and can't be overridden by simply replacing a CSS file. We'll need to create our own custom CSS file and add it to the assets bundle to override the default CSS rules.

First, let's create a new `assets/css/login-page.css` file and add the following code to that file. This will invert the colors of the background and login box on the login page. Feel free to make whatever styling changes you want on that page here.

**app/assets/css/login-page.css**
```css
.login-page {
    background-color: #ffffff;
}

.login-box-body {
    background-color: #d2d6de;
}
```

Second, we need to create the page ES module, to add our custom CSS file. Note the page Javascript, from AdminLTE theme, need to be defined here:

**app/assets/login-page.js**
```js
import 'theme-adminlte/app/assets/userfrosting/js/pages/sign-in';
import './css/login-page.css';
```

[notice=note]It could also be possible to define a separate entry, and load both the default entry and our custom entry on the same page[/notice]

Next, we need to replace the `page.sign-in` webpack entry. In your Sprinkle's `/webpack.entries.js`, add this entry:

**webpack.entries.js**
```js
module.exports = {
    // ...
    'page.sign-in': './app/assets/sign-in',
    // ...
}
```

Next, the login page doesn't have a page CSS by default. We need to fix this by extending the default page template:

**app/templates/pages/sign-in.html.twig**
```html
{% extends "@adminlte-theme/pages/sign-in.html.twig" %}

{% block stylesheets_page %}
    {{ encore_entry_link_tags('page.sign-in') }}
{% endblock %}
```

Last, we need to rebuild the assets, by running the bakery bake command : 

```bash
php bakery bake
```

Your new CSS file should be loaded when you refresh the page and you should see the result:

![Custom login style](/images/custom-login2.png)
