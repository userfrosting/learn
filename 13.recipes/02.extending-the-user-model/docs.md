---
title: Extending the User Model
metadata:
    description: Extend the user model, adding custom fields and views.
taxonomy:
    category: docs
---

One of the most common questions we get from new UserFrosting developers is "how do I add new user fields?"

Since every aspect of UF is extendable, there are a number of ways to go about this.  This tutorial just outlines one approach - you should consider the specific requirements of your application and users before deciding if this would be the best approach for you.

Our general constraints are:

1. We will avoid modifying the `users` table directly.  This will make it easier to integrate any future updates to UF that affect the `users` table.  It will also help prevent collisions with any community Sprinkles that modify the `users` table.  Instead, we will create a separate table, that has a one-to-one relationship with the `users` model.
2. We will avoid overriding controller methods as much as possible.  Controller methods tend to be longer and more complex than methods in our models, so again, it will be more work to integrate changes to controllers in future updates to UserFrosting.  It will be much easier if instead we extend the data models whenever possible, implementing new methods that enhance the base models.  We can also take advantage of Eloquent's [event handlers](https://laravel.com/docs/5.3/eloquent#events) for model classes to hook in additional functionality.

## Set up your site Sprinkle

If you haven't already, set up your site Sprinkle, as per the instructions in ["Your First UserFrosting Site"](/sprinkles/first-site).  For the purposes of this tutorial, we will call our Sprinkle `extend-user`.

## Create a migration

Follow the directions in [Chapter 6](/database/extending-the-database) for creating a new migration in your Sprinkle.  For our example, let's assume we want to add the fields `city` and `country`:

```php
<?php
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;
    /**
     * Owler table
     */
    if (!$schema->hasTable('owlers')) {
        $schema->create('owlers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->unique();
            $table->string('city', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
        });
        echo "Created table 'owlers'..." . PHP_EOL;
    } else {
        echo "Table 'owlers' already exists.  Skipping..." . PHP_EOL;
    }
```

## Create your data models

First thing's first, we'll create a data model that corresponds to our new `owlers` table:

```php
<?php
namespace UserFrosting\Sprinkle\ExtendUser\Model;

use UserFrosting\Sprinkle\Core\Model\UFModel;

class Owler extends UFModel {

    public $timestamps = true;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "owlers";

    protected $fillable = [
        "user_id",
        "city",
        "country"
    ];

    /**
     * Directly joins the related user, so we can do things like sort, search, paginate, etc.
     */
    public function scopeJoinUser($query)
    {
        $query = $query->select('owlers.*');

        $query = $query->leftJoin('users', 'owlers.user_id', '=', 'users.id');

        return $query;
    }

    /**
     * Get the user associated with this owler.
     */
    public function user()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('user'), 'user_id');
    }
}
```

This should be placed in the `src/Model/` directory in your own Sprinkle.  Notice that we set three properties: `$timestamps`, which enables automatic `created_at` and `updated_at` timestamps for our model, `$table`, which should contain the name of your table, and `$fillable`, which should be an array of column names that you want to allow to be [mass assignable](https://laravel.com/docs/5.3/eloquent#mass-assignment) when creating new instances of the model.

We also define two methods.  `scopeJoinUser` allows us to automatically join the columns in the `users` table when we use Laravel's query builder to retrieve `owlers`.  For example:

```php
$owlers = Owler::where('city', 'London')->joinUser()->get();
```

The second method, `user()`, defines a [one-to-one relationship](https://laravel.com/docs/5.3/eloquent-relationships#one-to-one) between `Owler` and `User`.  This is similar to what `scopeJoinUser()` does, except that it actually creates a completely separate `User` object that you can access as a property of an `Owler`:

```php
$owler = Owler::where('city', 'London')->first();

// Get the associated user object
$user = $owler->user;
```

## Create a virtual model

Ok, so now we have our `Owler` model, which stores the additional fields for each user and is related to the `User` model via its `user_id` column.  But, how do we represent this relationship in our Eloquent models?  After all, the default `User` model that ships with UserFrosting has no idea that `Owler` even exists.

To bring the two entities together we'll create a third model, `OwlerUser`, which extends the base `User` model to make it aware of the `Owler`.  This **virtual model** will enable us to interact with columns in both tables as if they were part of a single record.

```php
<?php
namespace UserFrosting\Sprinkle\ExtendUser\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\ExtendUser\Model\Owler;

trait LinkOwler {
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootLinkOwler()
    {
        /**
         * Create a new Owler if necessary, and save the associated owler every time.
         */
        static::saved(function ($owlerUser) {
            $owlerUser->createRelatedOwlerIfNotExists();

            // When creating a new OwlerUser, it might not have had a `user_id` when the `owler`
            // relationship was created.  So, we should set it on the Owler if it hasn't been set yet.
            if (!$owlerUser->owler->user_id) {
                $owlerUser->owler->user_id = $owlerUser->id;
            }

            $owlerUser->owler->save();
        });
    }
}
    
class OwlerUser extends User {
    use LinkOwler;

    protected $fillable = [
        "user_name",
        "first_name",
        "last_name",
        "email",
        "locale",
        "theme",
        "group_id",
        "flag_verified",
        "flag_enabled",
        "last_activity_id",
        "password",
        "deleted_at",
        "city",
        "country"
    ];

    /**
     * Required to be able to access the `owler` relationship in Twig without needing to do eager loading.
     * @see http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     */
    public function __isset($name)
    {
        if (in_array($name, [
            'owler'
        ])) {
            return isset($this->owler);
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * Custom accessor for Owler property
     */
    public function getCityAttribute($value)
    {
        return (count($this->owler) ? $this->owler->city : '');
    }

    /**
     * Custom accessor for Owler property
     */
    public function getCountryAttribute($value)
    {
        return (count($this->owler) ? $this->owler->country : '');
    }

    /**
     * Get the owler associated with this user.
     */
    public function owler()
    {
        return $this->hasOne('\UserFrosting\Sprinkle\ExtendUser\Model\Owler', 'user_id');
    }

    /**
     * Custom mutator for Owler property
     */
    public function setCityAttribute($value)
    {
        $this->createRelatedOwlerIfNotExists();

        $this->owler->city = $value;
    }

    /**
     * Custom mutator for Owler property
     */
    public function setCountryAttribute($value)
    {
        $this->createRelatedOwlerIfNotExists();

        $this->owler->country = $value;
    }

    /**
     * If this instance doesn't already have a related Owler (either in the db on in the current object), then create one
     */
    protected function createRelatedOwlerIfNotExists()
    {
        if (!count($this->owler)) {
            $owler = new Owler([
                'user_id' => $this->id
            ]);

            $this->setRelation('owler', $owler);
        }
    }
}
```

There's a lot going on here, so just a quick tour:

- `LinkOwler` is a [trait](http://php.net/manual/en/language.oop5.traits.php) used to attach handlers to events for our model.  In this case, we use the `saved` event to tell Laravel to save the related `Owler` model any time the `OwlerUser` is saved.  It will also call `createRelatedOwlerIfNotExists` which...well, does exactly what the name says it does.
- We add `city` and `country` to the model's `fillable` attributes, so that they can be directly passed in to the `OwlerUser` model's constructor.
- The `__isset` method is overridden to allow Twig to automatically fetch the related `owler` object (e.g., `current_user.owler`).  See [this answer](http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957) for an explanation of why this is needed.
- We have two [custom accessor methods](https://laravel.com/docs/5.3/eloquent-mutators), `getCityAttribute` and `getCountryAttribute`, and two custom mutator methods, `setCityAttribute` and `setCountryAttribute`.  These allow us to interact with the new fields directly through the `OwlerUser` object (e.g., `$owlerUser->city` and `$owlerUser->country`), passing them through to the related `Owler` model.
- The `owler()` method defines the relationship with the underlying `Owler` object.

## Map your virtual model

The problem, of course, is that all of the controllers in the Sprinkle that _defined_ the `User` model, are still _using_ the `User` model (this is simply how inheritance works).

Fortunately, the default Sprinkles never directly reference the `User` class.  Instead, they use the [class mapper](/sprinkles/contents#dynamic-class-mapper).  All we need to do, then, is remap the class mapper's `user` identifier to our new class, `OwlerUser`.  This can be done by extending the `classMapper` service in a custom [service provider](/services/extending-services).

Create a class `ServicesProvider/ServicesProvider`, and a Sprinkle initializer class, `ExtendUser.php`:

```php
<?php

// In /app/sprinkles/site/src/ServicesProvider/ServicesProvider.php

namespace UserFrosting\Sprinkle\ExtendUser\ServicesProvider;

class ServicesProvider
{
    /**
     * Register extended user fields services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extend the 'classMapper' service to register model classes.
         *
         * Mappings added: OwlerUser
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\ExtendUser\Model\OwlerUser');
            return $classMapper;
        });
    }
}
```

 `ExtendUser.php`:

```
<?php

// In /app/sprinkles/site/src/ExtendUser.php

namespace UserFrosting\Sprinkle\ExtendUser;

use UserFrosting\Sprinkle\ExtendUser\ServicesProvider\ServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

/**
 * Bootstrapper class for the 'extend-user' sprinkle.
 *
 */
class ExtendUser extends Sprinkle
{
    /**
     * Register services.
     */
    public function init()
    {
        $serviceProvider = new ServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
```

Now, **anywhere** that the `user` identifier is used with the class mapper, for example:

```
$user = $classMapper->staticMethod('user', 'where', 'email', 'admin@example.com')->first();
$owler = $user->owler;
```

The class mapper will call the method on the `OwlerUser` class instead. 

>>>>> You might want your _own_ references to be overrideable by other Sprinkles that might be loaded later on.  In this case, you should use the class mapper in your own controllers as well.

## Override the `user.html.twig` template to display the new fields

If we want these new fields to actually show up in our application, we need to add them to our templates.  For example, if we add them to `components/forms/user.html.twig`, they will be available in user creation, editing, and viewing contexts.  So, let's do that by **copying** the default `components/forms/user.html.twig` from the `admin` Sprinkle to our own, and then adding `city` and `country`:

```twig
{% if 'address' not in form.fields.hidden %}
<div class="col-sm-6">
    <div class="form-group">
        <label>City</label>
        <div class="input-group js-copy-container">
            <span class="input-group-addon"><i class="fa fa-map-pin"></i></span>
            <input type="text" class="form-control" name="city" autocomplete="off" value="{{user.city}}" placeholder="City" {% if 'address' in form.fields.disabled %}disabled{% endif %}>
        </div>
    </div>
</div>
<div class="col-sm-6">
    <div class="form-group">
        <label>Country</label>
        <div class="input-group js-copy-container">
            <span class="input-group-addon"><i class="fa fa-map-pin"></i></span>
            <input type="text" class="form-control" name="country" autocomplete="off" value="{{user.country}}" placeholder="Country" {% if 'address' in form.fields.disabled %}disabled{% endif %}>
        </div>
    </div>
</div>
{% endif %}
```

Notice that we wrap them in a single `if` block.  By doing this, we are grouping them into a single logical unit, `address`, which we can use to decide whether or not to show both fields (for example, via access control).  If you need to control the fields individually, then you should wrap them each in their own `if` block with more specific names.

## Override (just a few) controllers

I know that we said that we didn't want to modify controllers, but in some cases it is unavoidable.  For example, the `UserController::pageInfo` method explicitly states the fields that should be displayed in the form.  So, we will need to copy and modify it to display the `city` and `country` fields.  Create a new `Controller/OwlerController.php` class:

```php
<?php
namespace UserFrosting\Sprinkle\ExtendUser\Controller;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Support\Exception\ForbiddenException;

class OwlerController extends UserController
{

}
```

and copy into it the `pageInfo` method from `Controller/UserController.php` in the `admin` Sprinkle.  The full method is too long to show here, but you should find the line that says:

```php
// Determine fields that currentUser is authorized to view
$fieldNames = ['name', 'email', 'locale'];
```

and add the `address` field.

We'll also need to link our endpoints up to this new controller method.  To do this, we'll create a new route file, `owlers.php`, in our Sprinkle's `routes/` directory:

```php
<?php
/**
 * Routes for administrative user management.  Overrides routes defined in routes://admin/users.php
 */
$app->group('/admin/users', function () {
    $this->get('/u/{user_name}', 'UserFrosting\Sprinkle\ExtendUser\Controller\OwlerController:pageInfo');
})->add('authGuard');
```

## Override schemas

Finally, we need to override our request schemas, `user/create.json` and `user/edit-info.json`, to allow the new `city` and `country` fields to be submitted during user creation and update requests.  Copy both of these from the `admin` Sprinkle's `schema/user/` directory to your own Sprinkle's `schema/user/` directory.  Add validation rules for the new fields to both schema:

```json
    "city" : {
        "validators" : {
            "length" : {
                "label" : "City",
                "min" : 1,
                "max" : 255,
                "message" : "VALIDATE.LENGTH_RANGE"
            }
        }
    },
    "country" : {
        "validators" : {
            "length" : {
                "label" : "Country",
                "min" : 1,
                "max" : 255,
                "message" : "VALIDATE.LENGTH_RANGE"
            }
        }
    }
```

That's it!  A full implementation of this can be found in the [`extend-user`](https://github.com/userfrosting/extend-user) repository.  Check it out!
