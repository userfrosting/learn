---
title: Extending the User model
description: Extend the user model, adding custom fields and views.
obsolete: true
---

> [!TIP]
> A complete example of this guide can be found on GitHub : [https://github.com/userfrosting/extend-user](https://github.com/userfrosting/extend-user)

One of the most common questions we get from new UserFrosting developers is "how do I add new user fields?"

Since every aspect of UserFrosting is extendable, there are a number of ways to go about this. This tutorial just outlines one approach - you should consider the specific requirements of your application and users before deciding if this would be the best approach for you.

Our general constraints are:

1. We will avoid modifying the `users` table directly. This will make it easier to integrate any future updates to UF that affect the `users` table. It will also help prevent collisions with any community Sprinkles that modify the `users` table. Instead, we will create a separate table, that has a one-to-one relationship with the `users` model.
2. We will avoid overriding controller methods as much as possible. Controller methods tend to be longer and more complex than methods in our models, so again, it will be more work to integrate changes to controllers in future updates to UserFrosting. It will be much easier if instead we extend the data models whenever possible, implementing new methods that enhance the base models. We can also take advantage of Eloquent's [event handlers](https://laravel.com/docs/10.x/eloquent#events) for model classes to hook in additional functionality.

> [!TIP]
> Don't forget to check out the [Community Sprinkles](https://github.com/search?q=topic%3Auserfrosting-sprinkle&type=Repositories). Some may provide easy ways to add custom profile fields to your users and groups.

If you haven't already, set up an empty UserFrosting install [using the Skeleton](https://learn.userfrosting.com/structure/introduction#the-app-skeleton-your-projects-template). For the purposes of this tutorial, we will call our Sprinkle `extend-user` with `App\ExtendUser` as a base namespace.

## Implement the data layer

### Create a migration

We'll use a migration to create an auxiliary table, `members`, that stores our additional user columns.

Follow the directions in [Database Migrations](database/migrations) for creating a new migration in your Sprinkle. For our example, let's assume we want to add the fields `city` and `country`:

```php
<?php
namespace App\ExtendUser\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable;
use UserFrosting\Sprinkle\Core\Database\Migration;

class MembersTable extends Migration
{
    public static $dependencies = [
        UsersTable::class,
    ];

    public function up(): void
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

    public function down(): void
    {
        $this->schema->drop('members');
    }
}
```

Notice that we set the primary key, `id`, to _also_ be a foreign key to the `users` table. This effectively locks the `users` and `members` tables together so that each user will have the same `id` across both tables.

### Create the auxiliary data model

We'll also need to create a data model that corresponds to our new `members` table. This is a very simple model, which really only exists so that Laravel can set up the relationship with the main user model:

```php
<?php

namespace App\ExtendUser\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * @property int    $id
 * @property string $city
 * @property string $country
 */
class MemberAux extends Model
{
    /**
     * The table doesn't have timestamps columns
     */
    public $timestamps = false;

    /**
     * @var string The name of the table for the current model. We defined it,
     *             because the table name is different than the model name
     */
    protected $table = 'members';

    /**
     * Define the fillable columns
     */
    protected $fillable = [
        'city',
        'country',
    ];
}
```

This should be placed in the `src/Database/Models/` directory in your own Sprinkle. Notice that we set three properties: `$timestamps`, to disable timestamps for this table (we already have them in our main `users` table), `$table`, which should contain the name of your table, and `$fillable`, which should be an array of column names that you want to allow to be [mass assignable](https://laravel.com/docs/10.x/eloquent#mass-assignment) when creating new instances of the model.

### Extend the User model

Ok, so now we have our `MemberAux` model, which exposes the additional fields for each user and is related to the `User` model via its primary `id` column. But, how do we represent this relationship in our Eloquent models? After all, the default `User` model that ships with UserFrosting has no idea that `MemberAux` even exists.

To bring the two entities together we'll create a third model, `Member`, which extends the base `User` model to make it aware of the additional columns in `members`. This model will enable us to interact with columns in both tables as if they were part of a single record.

```php
<?php
namespace App\ExtendUser\Database\Models;

use App\ExtendUser\Database\Scopes\MemberAuxScope;
use Illuminate\Database\Eloquent\Relations\HasOne;
use UserFrosting\Sprinkle\Account\Database\Models\User;

/**
 * @property MemberAux $aux
 * @property string    $city
 * @property string    $country
 */
class Member extends User
{
    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'group_id',
        'flag_verified',
        'flag_enabled',
        'last_activity_id',
        'password',
        'deleted_at',
        'city',
        'country',
    ];

    protected string $auxType = MemberAux::class;

    /**
     * Globally joins the `members` table to access additional properties.
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new MemberAuxScope());
    }

    /**
     * Create a new MemberAux if necessary, and save the associated member data every time.
     */
    protected static function booted(): void
    {
        static::saved(function (Member $member) {
            $member->createAuxIfNotExists();

            // Set the aux PK, if it hasn't been set yet
            if ($member->aux->id !== 0) {
                $member->aux->id = $member->id;
            }

            $member->aux->save();
        });
    }

    /**
     * Custom mutator for Member property.
     */
    public function setCityAttribute(string $value): void
    {
        $this->createAuxIfNotExists();
        $this->aux->city = $value;
    }

    /**
     * Custom mutator for Member property.
     */
    public function setCountryAttribute(string $value): void
    {
        $this->createAuxIfNotExists();
        $this->aux->country = $value;
    }

    /**
     * Relationship for interacting with aux model (`members` table).
     */
    public function aux(): HasOne
    {
        return $this->hasOne($this->auxType, 'id');
    }

    /**
     * If this instance doesn't already have a related aux model (either in the db on in the current object), then create one.
     */
    protected function createAuxIfNotExists(): void
    {
        // @phpstan-ignore-next-line aux can be null is not initiated
        if ($this->auxType != '' && is_null($this->aux)) {
            // Create aux model and set primary key to be the same as the main user's
            $aux = new $this->auxType();

            // Needed to immediately hydrate the relation.  It will actually get saved in the bootLinkMemberAux method.
            $this->setRelation('aux', $aux);
        }
    }
}
```

There's a lot going on here, so just a quick tour:

1. The `booted()` method is used to attach a handler to the event for our model. In this case, we use the `saved` event to tell Laravel to save the related `MemberAux` model any time the `Member` is saved. It will also call `createAuxIfNotExists` which... well, does exactly what the name says it does.

2. We add `city` and `country` to the model's `fillable` attributes, so that they can be directly passed in to the `Member` model's constructor.

3. We override the model's booting method to automatically add the [global scope](https://laravel.com/docs/10.x/eloquent#global-scopes), `MemberAuxScope`. This will automatically join the `members` table whenever we make queries through the `Member` model, allowing us to access the additional fields. We'll explain how to create this scope next.

4. We have two [custom mutator methods](https://laravel.com/docs/10.x/eloquent-mutators), `setCityAttribute` and `setCountryAttribute`. These allow us to modify the new fields directly through the `Member` object (e.g., `$member->city` and `$member->country`), passing them through to the related `MemberAux` model.

5. The `aux()` method defines the relationship with the underlying `MemberAux` object.

### Define a global scope to automatically join the tables

A global scope allows us to customize the query that Laravel issues under the hood when you use methods like `Member::all()`, `Member::where('city', 'Bloomington')->first()`, and other [Eloquent](https://laravel.com/docs/10.x/eloquent) features. To do this, we'll create a new class in `src/Database/Scopes/MemberAuxScope.php`:

```php
<?php

namespace App\ExtendUser\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MemberAuxScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $baseTable = $model->getTable();
        // Hardcode the table name here, or inject the model and use `getTable`
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

This class only has one method, `apply`, which takes the base query builder object (`$builder`) for the model and applies additional fluent constraints. In our case, we'll use `leftJoin` to join the `members` table, as well as `addSelect` to specify the additional columns that we want to retrieve from `members`. Notice that we now need to explicitly tell the query builder to retrieve all columns from `users`. Otherwise, `addSelect` will actually end up telling Laravel to replace its default `select *` query, and we'd get _only_ the `city` and `country` columns.

### Map the `Member` model

The problem, of course, is that all of the controllers in the Sprinkle that _defined_ the `User` model, are still _using_ the `User` model (this is simply how inheritance works).

Fortunately, the default Sprinkles never directly reference the `User` class. Instead, they **[inject](dependency-injection)** the `UserInterface`. All we need to do, then, is remap the `UserInterface` to our new class, `Member`. This can be done via [Autowire](dependency-injection/the-di-container#binding-interfaces) in a [service provider](services/extending-services).

Create a class `src/ServicesProvider/MemberModelService.php` : 

```php
<?php

namespace App\ExtendUser\ServicesProvider;

use App\ExtendUser\Database\Models\Member;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;

class MemberModelService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            UserInterface::class  => \DI\autowire(Member::class),
        ];
    }
}
```

Plus, add `MemberModelService` to your Sprinkle recipe:

```php 
public function getServices(): array
{
    return [
        MemberModelService::class,
    ];
}
```

Now, **anywhere** that the `UserInterface` is injected, for example:

```php
public function __construct(UserInterface $user)
{
    // ...
    $city = $user->city;
    // ...
}
```

The dependency injection container will reference the method or property on the `Member` class instead.

> [!TIP]
> You might want your _own_ references to be overridable by other Sprinkles that might be loaded later on. In this case, you should inject the `UserInterface` in your own classes as well.

## Extend the interface layer (controller and views)

### Override the `user.html.twig` template to display the new fields

If we want these new fields to actually show up in our application, we need to add them to our templates. For example, if we add them to `forms/user.html.twig`, they will be available in user creation and editing forms. So, let's do that by **copying** the default `forms/user.html.twig` from the `admin` Sprinkle to our own, and then adding `city` and `country`:

```html
{% if 'address' not in form.fields.hidden %}
<div class="col-sm-6">
    <div class="form-group">
        <label>City</label>
        <div class="input-group js-copy-container">
            <span class="input-group-addon"><i class="fa-solid fa-map-pin fa-fw"></i></span>
            <input type="text" class="form-control" name="city" autocomplete="off" value="{{user.city}}" placeholder="City" {% if 'address' in form.fields.disabled %}disabled{% endif %}>
        </div>
    </div>
</div>
<div class="col-sm-6">
    <div class="form-group">
        <label>Country</label>
        <div class="input-group js-copy-container">
            <span class="input-group-addon"><i class="fa-solid fa-map-pin fa-fw"></i></span>
            <input type="text" class="form-control" name="country" autocomplete="off" value="{{user.country}}" placeholder="Country" {% if 'address' in form.fields.disabled %}disabled{% endif %}>
        </div>
    </div>
</div>
{% endif %}
```

Notice that we wrap them in a single `if` block. By doing this, we are grouping them into a single logical unit, `address`, which we can use to decide whether or not to show both fields (for example, via access control). If you need to control the fields individually, then you should wrap them each in their own `if` block with more specific names.

### Override (just a few) controllers

I know that we've said before that we didn't want to modify controllers, but in some cases it is unavoidable. For example, the `UserFrosting\Sprinkle\Admin\Controller\User\UserPageAction` action explicitly states the fields that should be displayed in the form. So, we will need to extend it to display the `city` and `country` fields. Create a new `src/Controller/MemberModelService.php` class:

```php
<?php
namespace App\ExtendUser\Controller;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException as ExceptionsForbiddenException;
use UserFrosting\Sprinkle\Admin\Controller\User\UserPageAction;

class MemberPageAction extends UserPageAction
{

}
```

There's different ways to add the required information in an existing action. For example, you could replace the whole `handle` method or intercept the data it returns an modify it. We'll go for the first option now. Copy the `handle` method from `UserPageAction` of the admin Sprinkle. The full method is too long to show here, but you should find the line that says:

```php
// Determine fields that currentUser is authorized to view
$fieldNames = ['user_name', 'name', 'email', 'locale', 'group', 'roles'];
```

...and add the `city` and `country` fields in your copied method.

We'll also need to link the route endpoints to this new class. To do this, we'll once again use the dependency injector to remap the `UserPageAction` to our new `MemberPageAction` class : 

```php
<?php

namespace App\ExtendUser\ServicesProvider;

use App\ExtendUser\Controller\MemberPageAction; //<-- Add
use App\ExtendUser\Database\Models\Member;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Admin\Controller\User\UserPageAction;  //<-- Add

class MemberModelService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            UserInterface::class  => \DI\autowire(Member::class),
            UserPageAction::class => \DI\autowire(MemberPageAction::class),  //<-- Add
        ];
    }
}
```

> [!NOTE]
> Don't forget to register this new service class in your Sprinkle Recipe

### Override schemas

Finally, we need to override our request schemas, `requests/user/create.yaml` and `requests/user/edit-info.yaml`, to allow the new `city` and `country` fields to be submitted during user creation and update requests. Copy both of these from the `admin` Sprinkle's `schema/requests/user/` directory to your own Sprinkle's `schema/requests/user/` directory. Add validation rules for the new fields to both schema:

```yaml
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
