---
title: Extending the User model
metadata:
    description: Extend the user model, adding custom fields and views.
taxonomy:
    category: docs
---

One of the most common questions we get from new UserFrosting developers is "how do I add new user fields?"

Since every aspect of UF is extendable, there are a number of ways to go about this.  This tutorial just outlines one approach - you should consider the specific requirements of your application and users before deciding if this would be the best approach for you.

Our general constraints are:

1. We will avoid modifying the `users` table directly.  This will make it easier to integrate any future updates to UF that affect the `users` table.  It will also help prevent collisions with any community Sprinkles that modify the `users` table.  Instead, we will create a separate table, that has a one-to-one relationship with the `users` model.
2. We will avoid overriding controller methods as much as possible.  Controller methods tend to be longer and more complex than methods in our models, so again, it will be more work to integrate changes to controllers in future updates to UserFrosting.  It will be much easier if instead we extend the data models whenever possible, implementing new methods that enhance the base models.  We can also take advantage of Eloquent's [event handlers](https://laravel.com/docs/5.4/eloquent#events) for model classes to hook in additional functionality.

>>>>>> Don't forget to check out the [Community Sprinkles](https://github.com/search?q=topic%3Auserfrosting-sprinkle&type=Repositories). Some may provide easy ways to add custom profile fields to your users and groups.   

## Set up your site Sprinkle

If you haven't already, set up your site Sprinkle, as per the instructions in [Your First UserFrosting Site](/sprinkles/first-site).  For the purposes of this tutorial, we will call our Sprinkle `extend-user`.

## Implement the data layer

### Create a migration

We'll use a migration to create an auxiliary table, `members`, that stores our additional user columns.

Follow the directions in [Database Migrations](/database/migrations) for creating a new migration in your Sprinkle.  For our example, let's assume we want to add the fields `city` and `country`:

```php
<?php
namespace UserFrosting\Sprinkle\ExtendUser\Database\Migrations\v400;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class MembersTable extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable'
    ];

    public function up()
    {
        if (!$this->schema->hasTable('members')) {
            $this->schema->create('members', function (Blueprint $table) {
                $table->increments('id');
                $table->string('city', 255)->nullable();
                $table->string('country', 255)->nullable();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->foreign('id')->references('id')->on('users');
            });
        }
    }

    public function down()
    {
        $this->schema->drop('members');
    }
}
```

Notice that we set the primary key, `id`, to _also_ be a foreign key to the `users` table. This effectively locks the `users` and `members` tables together so that each user will have the same `id` across both tables.

### Create the auxiliary data model

We'll also need to create a data model that corresponds to our new `members` table.  This is a very simple model, which really only exists so that Laravel can set up the relationship with the main user model:

```php
<?php

namespace UserFrosting\Sprinkle\ExtendUser\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

class MemberAux extends Model
{
    public $timestamps = false;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'members';

    protected $fillable = [
        'city',
        'country'
    ];
}
```

This should be placed in the `src/Database/Models/` directory in your own Sprinkle.  Notice that we set three properties: `$timestamps`, to disable timestamps for this table (we already have them in our main `users` table), `$table`, which should contain the name of your table, and `$fillable`, which should be an array of column names that you want to allow to be [mass assignable](https://laravel.com/docs/5.4/eloquent#mass-assignment) when creating new instances of the model.

### Extend the User model

Ok, so now we have our `MemberAux` model, which exposes the additional fields for each user and is related to the `User` model via its primary `id` column.  But, how do we represent this relationship in our Eloquent models?  After all, the default `User` model that ships with UserFrosting has no idea that `MemberAux` even exists.

To bring the two entities together we'll create a third model, `Member`, which extends the base `User` model to make it aware of the additional columns in `members`.  This model will enable us to interact with columns in both tables as if they were part of a single record.

```php
<?php
namespace UserFrosting\Sprinkle\ExtendUser\Database\Models;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\ExtendUser\Database\Models\MemberAux;
use UserFrosting\Sprinkle\ExtendUser\Database\Scopes\MemberAuxScope;

trait LinkMemberAux
{
    /**
     * The "booting" method of the trait.
     *
     * @return void
     */
    protected static function bootLinkMemberAux()
    {
        /**
         * Create a new MemberAux if necessary, and save the associated member data every time.
         */
        static::saved(function ($member) {
            $member->createAuxIfNotExists();

            if ($member->auxType) {
                // Set the aux PK, if it hasn't been set yet
                if (!$member->aux->id) {
                    $member->aux->id = $member->id;
                }

                $member->aux->save();
            }
        });
    }
}

class Member extends User
{
    use LinkMemberAux;

    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'theme',
        'group_id',
        'flag_verified',
        'flag_enabled',
        'last_activity_id',
        'password',
        'deleted_at',
        'city',
        'country'
    ];

    protected $auxType = 'UserFrosting\Sprinkle\ExtendUser\Database\Models\MemberAux';

    /**
     * Required to be able to access the `aux` relationship in Twig without needing to do eager loading.
     * @see http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     */
    public function __isset($name)
    {
        if (in_array($name, [
            'aux'
        ])) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * Globally joins the `members` table to access additional properties.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new MemberAuxScope);
    }

    /**
     * Custom mutator for Member property
     */
    public function setCityAttribute($value)
    {
        $this->createAuxIfNotExists();

        $this->aux->city = $value;
    }

    /**
     * Custom mutator for Member property
     */
    public function setCountryAttribute($value)
    {
        $this->createAuxIfNotExists();

        $this->aux->country = $value;
    }

    /**
     * Relationship for interacting with aux model (`members` table).
     */
    public function aux()
    {
        return $this->hasOne($this->auxType, 'id');
    }

    /**
     * If this instance doesn't already have a related aux model (either in the db on in the current object), then create one
     */
    protected function createAuxIfNotExists()
    {
        if ($this->auxType && !count($this->aux)) {
            // Create aux model and set primary key to be the same as the main user's
            $aux = new $this->auxType;

            // Needed to immediately hydrate the relation.  It will actually get saved in the bootLinkMemberAux method.
            $this->setRelation('aux', $aux);
        }
    }
}
```

There's a lot going on here, so just a quick tour:

- `LinkMemberAux` is a [trait](http://php.net/manual/en/language.oop5.traits.php) used to attach handlers to events for our model.  In this case, we use the `saved` event to tell Laravel to save the related `MemberAux` model any time the `Member` is saved.  It will also call `createAuxIfNotExists` which...well, does exactly what the name says it does.
- We add `city` and `country` to the model's `fillable` attributes, so that they can be directly passed in to the `Member` model's constructor.
- The `__isset` method is overridden to allow Twig to automatically fetch the related `MemberAux` object (e.g., `current_user.aux`).  See [this answer](http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957) for an explanation of why this is needed.
- We override the model's booting method to automatically add the [global scope](https://laravel.com/docs/5.4/eloquent#global-scopes), `MemberAuxScope`. This will automatically join the `members` table whenever we make queries through the `Member` model, allowing us to access the additional fields.  We'll explain how to create this scope next.
- We have two [custom mutator methods](https://laravel.com/docs/5.4/eloquent-mutators), `setCityAttribute` and `setCountryAttribute`.  These allow us to modify the new fields directly through the `Member` object (e.g., `$member->city` and `$member->country`), passing them through to the related `MemberAux` model.
- The `aux()` method defines the relationship with the underlying `MemberAux` object.

### Define a global scope to automatically join the tables

A global scope allows us to customize the query that Laravel issues under the hood when you use methods like `Member::all()`, `Member::where('city', 'Bloomington')->first()`, and other [Eloquent](https://laravel.com/docs/5.4/eloquent) features.  To do this, we'll create a new class in `src/Database/Scopes/MemberAuxScope.php`:

```php
<?php

namespace UserFrosting\Sprinkle\ExtendUser\Database\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MemberAuxScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $baseTable = $model->getTable();
        // Hardcode the table name here, or you can access it using the classMapper and `getTable`
        $auxTable = 'members';

        // Specify columns to load from base table and aux table
        $builder->addSelect(
            "$baseTable.*",
            "$auxTable.city as city",
            "$auxTable.country as country"
        );

        // Join on matching `member` records
        $builder->leftJoin($auxTable, function ($join) use ($baseTable, $auxTable) {
            $join->on("$auxTable.id", '=', "$baseTable.id");
        });
    }
}
```

This method only has one method, `apply`, which takes the base query builder object (`$builder`) for the model and applies additional fluent constraints. In our case, we'll use `leftJoin` to join the `members` table, as well as `addSelect` to specify the additional columns that we want to retrieve from `members`.  Notice that we now need to explicitly tell the query builder to retrieve all columns from `users`. Otherwise, `addSelect` will actually end up telling Laravel to replace its default `select *` query, and we'd get _only_ the `city` and `country` columns. 

### Map the `Member` model

The problem, of course, is that all of the controllers in the Sprinkle that _defined_ the `User` model, are still _using_ the `User` model (this is simply how inheritance works).

Fortunately, the default Sprinkles never directly reference the `User` class.  Instead, they use the [class mapper](/advanced/class-mapper).  All we need to do, then, is remap the class mapper's `user` identifier to our new class, `Member`.  This can be done by extending the `classMapper` service in a custom [service provider](/services/extending-services).

Create a class `ServicesProvider/ServicesProvider`, if you don't already have one:

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
         * Mappings added: Member
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\ExtendUser\Database\Models\Member');
            return $classMapper;
        });
    }
}
```

Now, **anywhere** that the `user` identifier is used with the class mapper, for example:

```
$user = $classMapper->staticMethod('user', 'where', 'email', 'admin@example.com')->first();
$city = $user->city;
```

The class mapper will call the method or property on the `Member` class instead. 

>>>>> You might want your _own_ references to be overrideable by other Sprinkles that might be loaded later on.  In this case, you should use the class mapper in your own controllers as well.

## Extend the interface layer (controller and views)

### Override the `user.html.twig` template to display the new fields

If we want these new fields to actually show up in our application, we need to add them to our templates.  For example, if we add them to `forms/user.html.twig`, they will be available in user creation and editing forms.  So, let's do that by **copying** the default `forms/user.html.twig` from the `admin` Sprinkle to our own, and then adding `city` and `country`:

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

### Override (just a few) controllers

I know that we said that we didn't want to modify controllers, but in some cases it is unavoidable.  For example, the `UserController::pageInfo` method explicitly states the fields that should be displayed in the form.  So, we will need to copy and modify it to display the `city` and `country` fields.  Create a new `Controller/MemberController.php` class:

```php
<?php
namespace UserFrosting\Sprinkle\ExtendUser\Controller;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Support\Exception\ForbiddenException;

class MemberController extends UserController
{

}
```

and copy into it the `pageInfo` method from `Controller/UserController.php` in the `admin` Sprinkle.  The full method is too long to show here, but you should find the line that says:

```php
// Determine fields that currentUser is authorized to view
$fieldNames = ['user_name', 'name', 'email', 'locale', 'group', 'roles'];
```

and add the `address` field in your copied method.

We'll also need to link our endpoints up to this new controller method.  To do this, we'll create a new route file, `members.php`, in our Sprinkle's `routes/` directory:

```php
<?php
/**
 * Routes for administrative user management.  Overrides routes defined in routes://admin/users.php
 */
$app->group('/admin/users', function () {
    $this->get('/u/{user_name}', 'UserFrosting\Sprinkle\ExtendUser\Controller\MemberController:pageInfo');
})->add('authGuard');
```

### Override schemas

Finally, we need to override our request schemas, `requests/user/create.yaml` and `requests/user/edit-info.yaml`, to allow the new `city` and `country` fields to be submitted during user creation and update requests.  Copy both of these from the `admin` Sprinkle's `schema/requests/user/` directory to your own Sprinkle's `schema/requests/user/` directory.  Add validation rules for the new fields to both schema:

```json
city:
  validators:
    length:
      label: City
      min: 1
      max: 255
      message: VALIDATE.LENGTH_RANGE
country:
  validators:
    length:
      label: Country
      min: 1
      max: 255
      message: VALIDATE.LENGTH_RANGE
```

That's it!  A full implementation of this can be found in the [`extend-user`](https://github.com/userfrosting/extend-user) repository.  Check it out!
