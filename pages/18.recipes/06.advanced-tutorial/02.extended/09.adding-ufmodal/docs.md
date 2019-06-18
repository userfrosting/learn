---
title: The Create modal form
metadata:
    description: Adding form modals to allow Pastries to be created and deleted.
taxonomy:
    category: docs
---

At this point you should be able to navigate to the `/pastries` page and see a table with the default data we have added. Seeds are great for initial setup but you will probably want the ability to dynamically add, edit, and delete rows from the table without relying on a seed. Let's add `create`, `edit`, and `delete` buttons to our table that will launch corresponding modal forms.

You should have already created the necessary directories and files earlier in the tutorial. If not, [go back](/recipes/advanced-tutorial/extended/base-setup#template-directories) and do that now.

We will begin by creating our ufForm and ufModal Twig template files, add the neccessary routes to our route file, and finally add code to our controller. For each modal we will need to add two functions to our controller - one to display the modal and one to complete the appropriate task upon form submission.  

### ufForm template

Instead of creating one form template and one modal template for both our `create` and `edit` buttons (four files total) we will create a dynamic template that can be used for either task. Let's begin by adding a standard html `<form>` tag to our Twig template. Rather than hardcoding the form `method` and `action` we will set these as Twig variables. This will allow us to dynamically change the `method` and `action` as needed in our controller.

We will also include `forms/csrf.html.twig` to protect against [CSRF](/routes-and-controllers/client-input/csrf-guard#injecting-the-tokens-into-forms) and an empty `<div>` with the class `js-form-alerts` which will be used to display validation messages. We will also add in a `script` block that will be used for validation.

`templates/forms/pastries.html.twig`

```js
<form class="js-form" method="{{form.method | default('POST')}}" action="{{site.uri.public}}/{{form.action}}">
    {% include "forms/csrf.html.twig" %}
    <div class="js-form-alerts">
    </div>

    <script>
{% include "pages/partials/page.js.twig" %}
</script>
</form>
```

Lets add in code for each of our table columns. The final file should look like:

`templates/forms/pastries.html.twig`

```js
<form class="js-form" method="{{form.method | default('POST')}}" action="{{site.uri.public}}/{{form.action}}">
    {% include "forms/csrf.html.twig" %}
    <div class="js-form-alerts">
    </div>
    <div class="row">
        {% block group_form %}
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Name</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-tag fa-fw"></i></span>
                        <input type="text" class="form-control" name="name" autocomplete="off" value="{{pastry.name}}" placeholder="The name of pastry">
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Origin</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-globe fa-fw"></i></span>
                        <input type="text" class="form-control" name="origin" autocomplete="off" value="{{pastry.origin}}" placeholder="The origin of pastry">
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Description</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-tag fa-fw"></i></span>
                        <input type="text" class="form-control" name="description" autocomplete="off" value="{{pastry.description}}" placeholder="A description of pastry">
                    </div>
                </div>
            </div>
        {% endblock %}
    </div>
    <div class="row">
        <div class="col-xs-6 col-sm-4">
            <button type="submit" class="btn btn-block btn-lg btn-success">{{form.submit_text}}</button>
        </div>
        <div class="col-xs-4 col-sm-3 pull-right">
            <button type="button" class="btn btn-block btn-lg btn-link" data-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>

<script>
{% include "pages/partials/page.js.twig" %}
</script>
```

You can review the chapter on [ufTable](/client-side-code/components/forms) for additional functionality and options when working with ufForm.

### ufModal template

The Twig template file for ufModal pretty basic. Set the modal title inside the `modal_title` block and then `include` our form template file inside the `modal_body` block.

`modals/pastries.html.twig`

```
{% extends "modals/modal.html.twig" %}

{% block modal_title %}Pastry{% endblock %}

{% block modal_body %}
    {% include "forms/pastries.html.twig" %}
{% endblock %}
```

### ufModal delete template

Next we will create the modal for the delete button. Because this is a basic form we will not create a separate Twig file for our form element and instead include it directly.

`modals/confirm-delete-pastries.html.twig`

```
{% extends "modals/modal.html.twig" %}

{% block modal_title %}Delete Pastry{% endblock %}

{% block modal_body %}
<form class="js-form" method="delete" action="{{site.uri.public}}/{{form.action}}">
    {% include "forms/csrf.html.twig" %}
    <div class="js-form-alerts">
    </div>
    <h4>Are you sure you want to delete {{pastry.name}}?<br><small>This cannot be undone!</small></h4>
    <br>
    <div class="btn-group-action">
        <button type="submit" class="btn btn-danger btn-lg btn-block">Delete</button>
        <button type="button" class="btn btn-default btn-lg btn-block" data-dismiss="modal">Cancel</button>
    </div>
</form>
{% endblock %}
```

### Validation schema

Next we need to create our request schema for validation. We will use a single file named `pastry.yaml` for both the `create` and `edit` forms. The chapter on [validation](/routes-and-controllers/client-input/validation) provides complete information on UserFrosting's validation process. Your directory structure and schema file should look like:

```
pastries
├──schema
   ├──requests
      ├──pastry
         └── pastry.yaml
```

`schema/requests/pastry/pastry.yaml`

```
---
name:
  validators:
    required:
      label: "&NAME"
      message: VALIDATE.REQUIRED
    length:
      label: "&NAME"
      min: 1
      max: 255
      message: VALIDATE.LENGTH_RANGE
  transformations:
  - trim
origin:
  validators:
    required:
      label: "&ORIGIN"
      message: VALIDATE.REQUIRED
    length:
      label: "&ORIGIN"
      min: 1
      max: 255
      message: VALIDATE.LENGTH_RANGE
  transformations:
  - trim
description:
  validators:
    length:
      label: "&DESCRIPTION"
      min: 1
      max: 65535
      message: VALIDATE.LENGTH_RANGE
  transformations:
```

### Adding the routes

Next add three routes to each the `/pastries/modals` group and  the `/api/pastries` group inside our route file.

```
// These routes will be used to access any modals
$app->group('/modals/pastries', function ()
    $this->get('/create', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalCreate');

    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalDelete');

    $this->get('/edit', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalEdit');
})->add('authGuard');

// These routes will for any methods that retrieve/modify data from the database.
$app->group('/api/pastries', function () {
    $this->delete('/p/{name}', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:delete');

    $this->post('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:create');

    $this->put('/p/{name}', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:updateInfo');
})->add('authGuard')->add(new NoCache());
```

Your finalized route file should look like this:

`app/sprinkles/pastries/routes/pastries.php`

```php
<?php

use UserFrosting\Sprinkle\Core\Util\NoCache;

$app->group('/pastries', function () {
    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:pageList')
         ->setName('pastries');
})->add('authGuard');

// These routes will for any methods that retrieve/modify data from the database.
$app->group('/api/pastries', function () {
    $this->delete('/p/{name}', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:delete');

    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getList');

    $this->post('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:create');

    $this->put('/p/{name}', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:updateInfo');
})->add('authGuard')->add(new NoCache());

// These routes will be used to store any modals
$app->group('/modals/pastries', function () {
    $this->get('/create', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalCreate');

    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalDelete');

    $this->get('/edit', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalEdit');
})->add('authGuard');
```

## Adding to the controller

We now need to add additional functions to our controller. Each of the routes we added in the previous step will require two more functions inside our controller class. The first function will retrieve the modal form and send it to the client and the second function will actually process the form submission. We will begin by examining the code that calls our modal to create a new pastry and break down what is happening.

### Create

#### `getModalCreate` function

```php
public function getModalCreate(Request $request, Response $response, $args)
{
    /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    /** @var \UserFrosting\I18n\MessageTranslator $translator */
    $translator = $this->ci->translator;

    // Load validation rules
    $schema = new RequestSchema('schema://requests/pastry/pastry.yaml');
    $validator = new JqueryValidationAdapter($schema, $translator);

    // Create a dummy pastry to prepopulate fields
    $pastry = new Pastries();

    return $this->ci->view->render($response, 'modals/pastries.html.twig', [
        'pastry' => $pastry,
        'form'   => [
            'action'      => 'api/pastries',
            'method'      => 'POST',
            'submit_text' => 'Create',
        ],
        'page' => [
            'validators' => $validator->rules('json', false),
        ],
    ]);
}
```

We do not want just anyone to be able to create new Pastries so the first thing we do is perform an [access check](/users/access-control#performing-access-checks) to ensure the user requesting the modal is authorized to do so. We begin by assigning an instance of the [authorizer service](/services/default-services#authorizer) to the  `$authorizer` variable and an instance of the [currentUser service](/services/default-services#currentUser) to the `$currentUser` variable.

```php
/** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
$authorizer = $this->ci->authorizer;

/** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
$currentUser = $this->ci->currentUser;

// Access-controlled page
if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
    throw new ForbiddenException();
}
```

Next, we use the `pastry.yaml` request schema to generate  our client-side [validation rules](/routes-and-controllers/client-input/validation#generating-client-side-rules) and assign them to the variable `$validator`. An empty instance of the `Pastries` model is then assigned to the variable `$pastry`.

```php
/** @var \UserFrosting\I18n\MessageTranslator $translator */
$translator = $this->ci->translator;

// Load validation rules
$schema = new RequestSchema('schema://requests/pastry/pastry.yaml');
$validator = new JqueryValidationAdapter($schema, $translator);

// Create a dummy pastry to prepopulate fields
$pastry = new Pastries();

```

Noticed that the `/modals/pastries.html.twig` Twig template is being sent with the response in addition to the form `action`, `method`, and `submit_text`  also being set here. The validation rules we generated above are also sent with the response.

```php
return $this->ci->view->render($response, 'modals/pastries.html.twig', [
    'pastry' => $pastry,
    'form'   => [
        'action'      => 'api/pastries',
        'method'      => 'POST',
        'submit_text' => 'Create',
    ],
    'page' => [
        'validators' => $validator->rules('json', false),
    ],
]);
```

>>>>> For simplicity we will use the `see_pastries` permission throughout the rest of this tutorial. However, you will probably want to add [custom permissions](/recipes/advanced-tutorial/custom-permissions) in your own Sprinkle to provide more granular control over what your users can do.

#### `create` function

Next, we need to create the code that will process the data after modal form submission. Let's look at the complete code and then go back through and break down the different "chunks" inside. Some of these "chunks" will be similar to the function we used to retrieve the modal (such as the authorizer service) and we will not cover those again.

```php
public function create(Request $request, Response $response, $args)
{
    // Get POST parameters: name, origin, description
    $params = $request->getParsedBody();

    /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
    $ms = $this->ci->alerts;

    // Load the request schema
    $schema = new RequestSchema('schema://requests/pastry/pastry.yaml');

    // Whitelist and set parameter defaults
    $transformer = new RequestDataTransformer($schema);
    $data = $transformer->transform($params);

    $error = false;

    // Validate request data
    $validator = new ServerSideValidator($schema, $this->ci->translator);
    if (!$validator->validate($data)) {
        $ms->addValidationErrors($validator);
        $error = true;
    }

    // Check if a pastry with this name already exists
    if (Pastries::where('name', $params['name'])->first()) {
        $ms->addMessageTranslated('danger', 'This pastry name is already in use.', $data);
        $error = true;
    }

    if ($error) {
        return $response->withJson([], 400);
    }

    // All checks passed!  log events/activities and create pastry
    // Begin transaction - DB will be rolled back if an exception occurs
    Capsule::transaction(function () use ($data, $ms, $currentUser) {
        // Create the pastry
        $pastry = new Pastries($data);

        // Store new pastry to database
        $pastry->save();

        // Create activity record
        $this->ci->userActivityLogger->info("User {$currentUser->user_name} created pastry {$pastry->name}.", [
          'type'    => 'pastry_create',
          'user_id' => $currentUser->id,
      ]);

        $ms->addMessageTranslated('success', 'New pastry created!', $data);
    });

    return $response->withJson([], 200);
}
```

The first few lines of code [assign the body parameters](/routes-and-controllers/client-input#retrieving-body-parameters) to the `$params` variable and then assign an instance of the [alert stream service](/services/default-services#alerts) to the $ms variable.

```   
$params = $request->getParsedBody();

// ....

$ms = $this->ci->alerts;
```

Next, we will use the `pastry.yaml` request schema to whitelist and filter the submitted data before then performing [server-side validation](/routes-and-controllers/client-input/validation#server-side-validation). We also run an Eloquent query to check if there is already a database record with the same name. If either of these actions fails then variable `$error` is set to `true` and the function ends by returning `  return $response->withJson([], 400);`.

```php
// Load the request schema
$schema = new RequestSchema('schema://requests/pastry/pastry.yaml');

// Whitelist and set parameter defaults
$transformer = new RequestDataTransformer($schema);
$data = $transformer->transform($params);

$error = false;

// Validate request data
$validator = new ServerSideValidator($schema, $this->ci->translator);
if (!$validator->validate($data)) {
    $ms->addValidationErrors($validator);
    $error = true;
}

// Check if a pastry with this name already exists
if (Pastries::where('name', $params['name'])->first()) {
    $ms->addMessageTranslated('danger', 'This pastry name is already in use.', $data);
    $error = true;
}

if ($error) {
    return $response->withJson([], 400);
}
```

The final chunk of code is wrapped inside an Eloquent [Database Transaction](https://laravel.com/docs/5.8/database#database-transactions), which means if any errors occur the transaction will be rolled back automatically. A new instance of the `Pastries` model is created with the data `$data` from our form submission and assigned to the variable `$pastry` and then the [`save`](https://laravel.com/docs/5.8/eloquent#inserting-and-updating-models) method is called.

A new entry is then added to the [User Activity Logger service](services/default-services#useractivitylogger) so that there is a record of when this new Pastry was created. Finally, a `success` [alert](/routes-and-controllers/alert-stream#adding-messages-to-the-alert-stream) is sent to the user before returning a [`200` response](routes-and-controllers/rest/restful-responses#200-ok-).

```
Capsule::transaction(function () use ($data, $ms, $currentUser) {
    // Create the pastry
    $pastry = new Pastries($data);

    // Store new pastry to database
    $pastry->save();

    // Create activity record
    $this->ci->userActivityLogger->info("User {$currentUser->user_name} created pastry {$pastry->name}.", [
      'type'    => 'pastry_create',
      'user_id' => $currentUser->id,
  ]);

    $ms->addMessageTranslated('success', 'New pastry created!', $data);
});

return $response->withJson([], 200);
```

### Edit
Both the create and edit controller functions have similar code so we will not cover similar snippets twice. Rather, we will be point out just the pieces that are unique.

#### `getModalEdit` function

 Here is how the function will look for retrieving the `edit` modal form:

```php
<?php
public function getModalEdit(Request $request, Response $response, $args)
{
    // GET parameters
    $params = $request->getQueryParams();

    $pastry = Pastries::where('name', $params['name'])->first();

    // If the postry doesn't exist, return 404
    if (!$pastry) {
        throw new NotFoundException();
    }

    /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
    $currentUser = $this->ci->currentUser;

    /** @var \UserFrosting\I18n\MessageTranslator $translator */
    $translator = $this->ci->translator;

    // Load validation rules
    $schema = new RequestSchema('schema://requests/pastry/pastry.yaml');
    $validator = new JqueryValidationAdapter($schema, $translator);

    return $this->ci->view->render($response, 'modals/pastries.html.twig', [
    'pastry' => $pastry,
    'form'   => [
        'action'      => "api/pastries/p/{$pastry->name}",
        'method'      => 'PUT',
        'submit_text' => 'Update',
    ],
    'page' => [
        'validators' => $validator->rules('json', false),
      ],
    ]);
}
```

As opposed to the `getModalCreate` function, this modal form is intended to modify an already existing database record and so the first thing that is done is to retrieve the request [query parameters](/routes-and-controllers/client-input#retrieving-url-parameters) and then perform and Eloquent query to retrieve that record from the database. If the record can not be found a `NotFoundException();` is thrown.

```php
// GET parameters
$params = $request->getQueryParams();

$pastry = Pastries::where('name', $params['name'])->first();

// If the postry doesn't exist, return 404
if (!$pastry) {
    throw new NotFoundException();
}
```

Notice that the form `action`, `method`, and `submit_text` are once again set in our controller code, allowing us to use the same Twig file template for both `create` and `edit`.

```
'form'   => [
    'action'      => "api/pastries/p/{$pastry->name}",
    'method'      => 'PUT',
    'submit_text' => 'Update',
],
```

The rest of the code in this function is similar enough to the [`getModalCreate`](/recipes/advanced-tutorial/extended/adding-ufmodal#create-function) that we will not cover it.

#### `updateInfo` function

```php
public function updateInfo(Request $request, Response $response, $args)
{
    $pastry = Pastries::where('name', $args['name'])->first();

    // If the pastry doesn't exist, return 404
    if (!$pastry) {
        throw new NotFoundException();
    }

    // Get PUT parameters: (name, origin, description)
    $params = $request->getParsedBody();

    /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
    $ms = $this->ci->alerts;

    // Load the request schema
    $schema = new RequestSchema('schema://requests/pastry/pastry.yaml');

    // Whitelist and set parameter defaults
    $transformer = new RequestDataTransformer($schema);
    $data = $transformer->transform($params);

    $error = false;

    // Validate request data
    $validator = new ServerSideValidator($schema, $this->ci->translator);
    if (!$validator->validate($data)) {
        $ms->addValidationErrors($validator);
        $error = true;
    }

    /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled action.
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    // Check if the name already exists.
    if (isset($data['name']) && $data['name'] != $pastry->name && Pastries::where('name', $data['name'])->first()) {
        $ms->addMessageTranslated('danger', 'A pastry with this name already exists.', $data);
        $error = true;
    }

    if ($error) {
        return $response->withJson([], 400);
    }

    // Begin transaction - DB will be rolled back if an exception occurs
    Capsule::transaction(function () use ($data, $pastry, $currentUser) {
        // Update the pastry and generate success messages
        foreach ($data as $name => $value) {
            if ($value != $pastry->$name) {
                $pastry->$name = $value;
            }
        }

        // Save the changes.
        $pastry->save();

        // Create activity record
        $this->ci->userActivityLogger->info("User {$currentUser->user_name} updated details for pastry {$pastry->name}.", [
            'type'    => 'pastry_update_info',
            'user_id' => $currentUser->id,
        ]);
    });

    $ms->addMessageTranslated('success', 'The pastry was updated!', [
        'name' => $pastry->name,
    ]);

    return $response->withJson([], 200);
}
}
```

The only unique chunk is the `foreach` block inside the `Capsule::transaction`. This loops through each field of data that was submitted and checks it against the existing database record. If the values do not match then the new values is assigned.

```php
// Update the pastry and generate success messages
foreach ($data as $name => $value) {
    if ($value != $pastry->$name) {
        $pastry->$name = $value;
    }
}
```

### Delete

#### `getModalDelete` function

```php
public function getModalDelete(Request $request, Response $response, $args)
{
    // GET parameters
    $params = $request->getQueryParams();

    $pastry = Pastries::where('name', $params['name'])->first();

    // If the pastry no longer exists, forward to main pastry listing page
    if (!$pastry) {
        throw new NotFoundException();
    }

    /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    return $this->ci->view->render($response, 'modals/confirm-delete-pastries.html.twig', [
        'pastry' => $pastry,
        'form'   => [
            'action' => "api/pastries/p/{$pastry->name}",
          ],
        ]);
}
```

#### `delete` function

```php
public function delete(Request $request, Response $response, $args)
{
    $pastry = Pastries::where('name', $args['name'])->first();

    // If the pastry doesn't exist, return 404
    if (!$pastry) {
        throw new NotFoundException();
    }

    /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
    $authorizer = $this->ci->authorizer;

    /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    $pastryName = $pastry->name;

    // Begin transaction - DB will be rolled back if an exception occurs
    Capsule::transaction(function () use ($pastry, $pastryName, $currentUser) {
        $pastry->delete();
        unset($pastry);

        // Create activity record
        $this->ci->userActivityLogger->info("User {$currentUser->user_name} deleted pastry {$pastryName}.", [
            'type'    => 'pastry_delete',
            'user_id' => $currentUser->id,
        ]);
    });

    /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
    $ms = $this->ci->alerts;

    $ms->addMessageTranslated('success', 'Pastry deleted!');

    return $response->withJson([], 200);
}
```
