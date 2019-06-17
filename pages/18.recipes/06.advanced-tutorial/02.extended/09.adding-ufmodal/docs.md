---
title: Adding Form Modals
metadata:
    description: Adding form modals to allow Pastries to be created and deleted.
taxonomy:
    category: docs
---

At this point you should be able to navigate to the `/pastries` page and see a table with the default data we have added. Seeds are great for initial setup but you will probably want the ability to dynamically add, edit, and delete rows from the table without relying on a seed. Let's add `create`, `edit`, and `delete` buttons to our table that will launch corresponding modal forms.

You should have already created the necessary directories and files earlier in the tutorial. If not, [go back](/recipes/advanced-tutorial/extended/base-setup#template-directories) and do that now.

We will begin by creating our ufForm and ufModal Twig template files, add the neccessary routes to our route file, and finally add code to our controller. For each modal we will need to add two functions to our controller - one to display the modal and one to complete the appropriate task upon form submission.  

### ufForm template

Instead of creating one form template and one modal template for both our `create` and `edit` buttons (four files total) we will create create a dynamic template that can be used for either task. Let's begin by adding a standard html `<form>` tag to our Twig template. Rather than hardcoding the form `method` and `action` we will set these as Twig variables. This will allow us to dynamically change the `method` and `action` as needed in our controller.

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

Let's create our srequest schema for client validation. If you need a refresher you can go read the chapter on [validation](/routes-and-controllers/client-input/validation). Your directory structure should look like:

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

We will now add three routes to the `/pastries/modals` group inside our route file.

```
// These routes will be used to access any modals
$app->group('/modals/pastries', function ()
    $this->get('/create', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalCreate');

    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalDelete');

    $this->get('/edit', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getModalEdit');
})->add('authGuard');
```

### Adding to the controller

We now need to add additional functions to our controller. Each of the routes we added in the previous step will require two more functions inside our controller class. Lets begin by examining the code that calls our modal to create a new pastry and break down what is happening.

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
    $schema = new RequestSchema('schema://requests/pastry/create.yaml');
    $validator = new JqueryValidationAdapter($schema, $translator);

    // Create a dummy pastry to prepopulate fields
    $pastry = new Pastries();

    $fields = [
        'hidden'   => [],
        'disabled' => [],
    ];

    return $this->ci->view->render($response, 'modals/pastries.html.twig', [
        'pastry' => $pastry,
        'form'   => [
            'action'      => 'api/pastries',
            'method'      => 'POST',
            'fields'      => $fields,
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

>>>>> We will use the [`see_pastries` permission ](/recipes/advanced-tutorial/custom-permissions) throughout the rest of this tutorial. However, you will probably want to add additional permissions in your own Sprinkle.
