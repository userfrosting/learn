---
title: Collections
metadata:
    description: The ufCollection widget provides a convenient interface for associating related or child entities with a single parent entity.
taxonomy:
    category: docs
process:
    twig: true
never_cache_twig: true
---

{% do assets.addCss('theme://css/select2.min.css') %}
{% do assets.addCss('theme://css/uf-collection.css') %}

{% do assets.addJs('theme://handlebars/handlebars.js') %}
{% do assets.addJs('theme://js/select2.full.min.js') %}
{% do assets.addJs('theme://jquery.inputmask/dist/inputmask/inputmask.js') %}
{% do assets.addJs('theme://jquery.inputmask/dist/inputmask/inputmask.extensions.js') %}
{% do assets.addJs('theme://jquery.inputmask/dist/inputmask/jquery.inputmask.js') %}
{% do assets.addJs('theme://jquery.inputmask/dist/inputmask/bindings/inputmask.binding.js') %}

{% do assets.addJs('theme://js/attrchange.js') %}
{% do assets.addJs('theme://js/uf-collection.js') %}
{% do assets.addJs('theme://js/serialize-controls.js') %}
{% do assets.addJs('theme://js/example-collection.js') %}

The `ufCollection` widget provides a convenient interface for associating related or child entities with a single parent entity. For example, you might want to associate a user account with one or more roles, or add multiple phone numbers or addresses to an employee.

![ufCollection widget as used for the 'user role' management interface.](/images/uf-collection.png)

## Basic setup

To use `ufCollection`, you need to have `userfrosting/js/uf-collection.js` and `userfrosting/css/uf-collection.css` included in your page assets. The easiest way to do this is by including the `js/form-widgets` and `css/form-widgets` [asset bundles](/asset-management/asset-bundles) in your page. Most of the default administrative pages include these bundles by default in their `stylesheets_page` and `scripts_page` Twig blocks. Of course, feel free to include the required JS and CSS files in your page-specific asset bundles instead, if you prefer.

The basic markup for a collection widget consists of a table "skeleton" wrapped inside some sort of container element. For example:

```html
<div id="member-phones">
    <label>Phone numbers</label>
    <table class="table table-striped">
        <tbody>
        </tbody>
    </table>
</div>
```

### Table rows

You'll notice that the table skeleton contains an empty `tbody` element. By default, when a new row is added (by the client or programmatically), `ufCollection` will insert the new row inside this `tbody`.

The markup for the rows themselves is dynamically generated using a [Handlebars template](/client-side-code/client-side-templating). The row template can be embedded inside your page's Twig template using the Twig `{{ '{% verbatim %}' }}` tag. It might look something like:

```
{{ '{% verbatim %}' }}
<script id="member-phones-row" type="text/x-handlebars-template">
    <tr class="uf-collection-row">
        <td>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone fa-fw"></i></span>
                <input type="hidden" name="phones[{{ rownum }}][id]" value="{{id}}">
                <input type="text" class="form-control" name="phones[{{ rownum }}][label]" value="{{label}}" placeholder="Label">
            </div>
        </td>
        <td>
            <input type="text" class="form-control" name="phones[{{ rownum }}][number]" value="{{number}}" placeholder="Number">
        </td>
        <td>
            <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
        </td>
    </tr>
</script>
{{ '{% endverbatim %}' }}
```

Some important features of the row template:

- The template should contain a single `tr` element with the `uf-collection-row` class.
- The last column of your row should be a "delete" button, allowing the user to remove associations. You may style this button however you like, but it needs to have the `js-delete-row` class for `ufCollection` to bind the delete event.
- The user can add additional information to each row via additional controls (`input`, `select`, etc).
- You should have a hidden `input` control in each row, that contains the `id` for the element associated with that row.
- We recommend submitting the entire collection under a single top-level `name` attribute. You can then use the `rownum` placeholder to index the elements of this key, and under that, the individual properties of the row.

For example, the submitted data from this collection might end up looking something like:

```json
    "phones": [
        {
            "id": 17,
            "label": "primary",
            "number": "5555551212"
        },
        {
            "id": "",
            "label": "mobile",
            "number": "1233219999"
        }
    ]
```

## Usage

`ufCollection` can be used to provide an interface for manipulating two types of relationships - one-to-many, and many-to-many.

For **one-to-many** relationships, the client can directly create, modify, and delete entities that should be associated with exactly one parent entity. For example, a user can have multiple phone numbers, but they only belong to that one user - users do not "share" the same phone number.

In a **many-to-many** relationship, the client selects pre-existing entities to match up to the parent entity. These entities might be shared with other parent entities as well - thus the "many to many". There might be some additional data associated with the relationship (so called "pivot data") that the client can add, but the related entity itself is shared. For example, a member can have the same model of car as other members, but the specific VIN of their particular car would be unique to them. Thus, they might preselect their model ("Mazda 3") from a dropdown, and then enter in their VIN as pivot data.

### One-to-many

In one-to-many mode, users directly enter data into controls in each row. For this reason, we sometimes refer to this as "free text" mode.

To initialize a one-to-many collection widget, call the `ufCollection` method on your container element:

```js
$('#member-phones').ufCollection({
    useDropdown: false,
    rowTemplate: $('#member-phones-row').html()
});
```

Notice that we have set `useDropdown` to `false`. When this is set to false, `ufCollection` will automatically add a new empty row below the last row that has been "touched". A row is "touched" whenever it is brought into focus, or when it is programmatically added using the `addRow` method. Thus, this ensures that the user can always add another row of information without needing to click an "add" button. Try the live demo below:

<div id="example-phones">
    <div style="float: left;">
        <label>Phone numbers</label>
        <div>
            <table class="table-striped">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="example-output">
        <label>Data received by server:</label>
        <pre>
            <code>
            </code>
        </pre>
    </div>
    <div style="clear: both;"></div>
</div>

{% verbatim %}
<script id="collection-phones-row" type="text/x-handlebars-template">
    <tr class="uf-collection-row">
        <td>
            <div class="input-group">
                <input type="hidden" name="phones[{{ rownum }}][id]" value="{{id}}">
                <input type="text" class="form-control" name="phones[{{ rownum }}][label]" value="{{label}}" placeholder="Label">
            </div>
        </td>
        <td>
            <input type="text" class="form-control js-input-phone" name="phones[{{ rownum }}][number]" data-inputmask="'mask': '(999) 999-9999', 'autoUnmask': true" data-mask autocomplete="off" value="{{number}}" placeholder="Number">
        </td>
        <td>
            <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
        </td>
    </tr>
</script>
{% endverbatim %}

#### Server-side processing

Let's assume that your `ufCollection` widget is part of a form that you will submit to the server for processing, and that you want to update the related entities in the database. If you used the naming scheme for your row controls as suggested in the [setup section](#table-rows), the rendered collection table might end up looking something like:

```html
<tr class="uf-collection-row">
    <td>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-phone fa-fw"></i></span>
            <input type="hidden" name="phones[0][id]" value="17">
            <input type="text" class="form-control" name="phones[0][label]" value="primary" placeholder="Label">
        </div>
    </td>
    <td>
        <input type="text" class="form-control" name="phones[0][number]" value="5555551212" placeholder="Number">
    </td>
    <td>
        <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
    </td>
</tr>
<tr class="uf-collection-row">
    <td>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-phone fa-fw"></i></span>
            <input type="hidden" name="phones[1][id]" value="">
            <input type="text" class="form-control" name="phones[1][label]" value="mobile" placeholder="Label">
        </div>
    </td>
    <td>
        <input type="text" class="form-control" name="phones[1][number]" value="1233219999" placeholder="Number">
    </td>
    <td>
        <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
    </td>
</tr>
```

The data submitted to the server will then end up looking like:

```
phones[0][id]: "17"
phones[0][label]: "primary"
phones[0][number]: "5555551212"
phones[1][id]: ""
phones[1][label]: "mobile"
phones[1][number]: "1233219999"
```

This is an easy format for the server to process, because PHP will automatically convert this into a multidimensional array:

```php
$phones = $request->getParsedBody()['phones'];
error_log(print_r($phones, true));
```

**Output:**

```
Array
(
    [0] => Array
        (
            [id] => 17,
            [label] => "primary",
            [number] => "5555551212"
        )

    [1] => Array
        (
            [id] => ,
            [label] => "mobile",
            [number] => "1233219999"
        )

)
```

Notice that one of our phone numbers has a set `id` value (17), while the other number has an empty/unset `id`. This is how we tell the server that the first number is an existing entity that should be updated, while the second is a completely new entity that should be created and associated with the parent entity.

UserFrosting implements a custom version of Laravel's [`hasMany` relationship](https://laravel.com/docs/5.8/eloquent-relationships#one-to-many), which allows you to synchronize the related entities for a parent entity:

```php
$member->phones()->sync($phones);
```

Entities in `$phones` that have an `id` that matches one of the related entities for the parent entity in the database will be updated. Entities with an empty `id`, or an `id` that does not match one of the parent's related entities, will be considered a new entry, and a record will be added to the database. Entities in the _database_ that do not match the _input_, on the other hand, will be considered "deleted" and removed from the database.

| Entity in submitted data | Entity in database     | Operation |
| ------------------------ | ---------------------- | --------- |
| Yes                      | Yes (matching id)      | Updated   |
| Yes                      | No                     | Created   |
| No                       | Yes (matching parent)  | Deleted   |

If you want to prevent entities from being deleted, even if they are not present in the input, you can set the second argument of `sync` to `false`.

### Many-to-many

Many-to-many collections require some additional markup in the skeleton - we need to add a `select` control for selecting preexisting items to add to the collection. For example, let's say we want to allow members to choose species of owls from a prepopulated list, and then give each selected owl a name:

<div id="example-member-owls">
    <div style="float: left;">
        <label>My owls</label>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Species</th>
                    <th>Name</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <div class="padding-bottom">
            <label>Adopt an owl:</label>
            <select class="form-control js-select-new" type="text" data-placeholder="Select an owl">
                <option></option>
            </select>
        </div>
    </div>
    <div class="example-output">
        <label>Data received by server:</label>
        <pre>
            <code>
            </code>
        </pre>
    </div>
    <div style="clear: both;"></div>
</div>

{% verbatim %}
<script id="example-member-owls-row" type="text/x-handlebars-template">
    <tr class="uf-collection-row">
        <td>
            {{species}}
            <input type="hidden" name="owls[{{ rownum }}][species_id]" value="{{id}}">
        </td>
        <td>
            <input type="text" name="owls[{{ rownum }}][name]" value="{{name}}">

        </td>
        <td>
            <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
        </td>
    </tr>
</script>

<script id="example-member-owls-select-option" type="text/x-handlebars-template">
    <div>
        <strong>
            {{species}}
        </strong>
        <br>
        {{description}}
    </div>
</script>
{% endverbatim %}

```
<div id="member-owls">
    <label>My owls</label>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Species</th>
                <th>Name</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div class="padding-bottom">
        <label>Adopt an owl:</label>
        <select class="form-control js-select-new" type="text" data-placeholder="Select an owl">
            <option></option>
        </select>
    </div>
</div>
```

The `select` control should have the class `js-select-new`, which tells `ufCollection` that we should add a new row to the collection **whenever a selection is made**. Internally `ufCollection` uses the [select2](http://select2.github.io/) library to create a beautiful, searchable dropdown menu.

To initialize the widget, we leave out the `useDropdown` argument (which defaults to true), and instead add the `dropdown` and `dropdownTemplate` options:

```js
$('#member-owls').ufCollection({
    dropdown: {
        ajax: {
            url: site.uri.public + '/api/owls'
        }
    },
    dropdownTemplate: $('#member-owls-select-option').html(),
    rowTemplate: $('#member-owls-row').html()
});
```

Our row template looks similar to that for one-to-many, except we name our hidden `id` input with the foreign key for the relationship. For example, we use `species_id` to refer to the `id` of the selected species for that row:

```html
{{ '{% verbatim %}' }}
<script id="member-owls-row" type="text/x-handlebars-template">
    <tr class="uf-collection-row">
        <td>
            {{species}}
            <input type="hidden" name="owls[{{ rownum }}][species_id]" value="{{id}}">
        </td>
        <td>
            <input type="text" name="owls[{{ rownum }}][name]" value="{{name}}">

        </td>
        <td>
            <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
        </td>
    </tr>
</script>
{{ '{% endverbatim %}' }}
```

The additional options we need to set for many-to-many are:

#### `dropdownTemplate`

The `dropdownTemplate` parameter should contain a valid Handlebars template that `ufCollection` can render using the data returned by the AJAX request.

As with the row template, we can define our dropdown template in our page's main Twig template:

```html
{{ '{% verbatim %}' }}
<script id="member-owls-select-option" type="text/x-handlebars-template">
    <div>
        <strong>
            {{species}}
        </strong>
        <br>
        {{description}}
    </div>
</script>
{{ '{% endverbatim %}' }}
```

As with the `rowTemplate` option, we can retrieve reference this template using a jQuery selector and the `.html()` method.

#### `dropdown`

This parameter contains the [Select2 options](http://select2.github.io/options.html) that should be passed in when `ufCollection` initializes the control. Other than the `ajax` key, you probably won't need to customize most of the `dropdown` options.

##### AJAX data source

For large, dynamic collections of options, you will probably want to provide an AJAX data source with the `ajax` subkey.

`ufCollection` will expect JSON data from an AJAX source, in the same format as that returned by the [Data Sprunjer](/database/data-sprunjing). In particular, it should have a `rows` key that contains the collection of selectable options:

```json
{
    "count": 2,
    "count_filtered": 2,
    "rows": [
        {
            "id": 11,
            "species": "Bubo scandiacus",
            "description": "Snowy owls are native to Arctic regions in North America and Eurasia. Males are almost all white, while females have more flecks of black plumage. Juvenile snowy owls have black feathers until they turn white. The snowy owl is a ground nester that predominantly hunts rodents."
        },
        {
            "id": 8,
            "species": "Megascops asio",
            "description": "This species is native to most wooded environments of its distribution and, more so than any other owl in its range, has adapted well to manmade development, although it frequently avoids detection due to its strictly nocturnal habits."
        }
    ]
}
```

If you would like to use an alternative format for your source data, you can override the `dropdown.ajax.processResults` [callback option](#dropdown-1).

#### Server-side processing

With the example many-to-many row template, the rendered table might end up looking something like:

```html
<tr class="uf-collection-row">
    <td>
        Megascops asio
        <input type="hidden" name="owls[1][species_id]" value="2">
    </td>
    <td>
        <input type="text" name="owls[1][name]" value="Slasher">
    </td>
    <td>
        <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
    </td>
</tr>
<tr class="uf-collection-row">
    <td>
        Megascops asio
        <input type="hidden" name="owls[2][species_id]" value="5">
    </td>
    <td>
        <input type="text" name="owls[2][name]" value="Fluffers">
    </td>
    <td>
        <button type="button" class="btn btn-link btn-trash js-delete-row pull-right" title="Delete"> <i class="fa fa-trash"></i> </button>
    </td>
</tr>
```

The data submitted to the server will then end up looking like:

```
owls[1][species_id]: "2"
owls[1][name]: "Slasher"
owls[2][species_id]: "5"
owls[2][name]: "Fluffers"
```

Again, PHP will automatically convert this into a multidimensional array:

```php
$owls = $request->getParsedBody()['owls'];
error_log(print_r($owls, true));
```

**Output:**

```
Array
(
    [1] => Array
        (
            [species_id] => 2,
            [name] => "Slasher"
        )

    [2] => Array
        (
            [species_id] => 5,
            [name]: =>"Fluffers"
        )

)
```

Laravel's `sync` method can synchronize our input data with the database for [many-to-many relationships](https://laravel.com/docs/5.8/eloquent-relationships#many-to-many):

```php
$owlsCollection = collect($owls)->pluck(['species_id', 'name'])->all();
$member->owls()->sync($owlsCollection);
```

The `collect` function will convert the raw multidimensional array into a collection of objects. `pluck` will then make sure that we only grab the values we're interested in. This is useful as a validation tool, to reject any fields that we don't want to allow the client to modify).

Finally, calling the [`sync` method](https://laravel.com/docs/5.8/eloquent-relationships#updating-many-to-many-relationships) on a member's `owls` relationship will update the entire relationship, so that the owls associated with the member match exactly the submitted data.

## Methods, events, and options

This section covers the methods, events, and options that you can use to programmatically control your `ufCollection` widget.

### Methods

Methods are called via the standard jQuery practice of invoking the plugin name with the method name as the first argument:

```js
$('#myCollection').ufCollection('methodName', methodOptions);
```

#### addRow

_Adds a new row to an existing `ufCollection` widget, optionally prepopulated with some data._

**Example usage:**

```js
// Get current phones from an AJAX source, and add to member phones
var memberId = 12;
$.getJSON(site.uri.public + '/api/members/m/' + memberId)
.done(function (data) {
    $.each(data.phones, function (idx, phone) {
        phoneCollection.ufCollection('addRow', phone);
    });
});
```

This pattern is especially useful when you want to provide an "update" feature for a collection. In the example above, we preload the collection with the member's current phone numbers (from an AJAX source), so that the user can add new phone numbers or remove current numbers. In this case, we expect the data source to have a `phones` key, which contains an array of phone number objects:

```json
{
    // Maybe some other member information here

    ...

    "phones": [
        {
            "id": 17,
            "label": "primary",
            "number": "5555551212"
        },
        {
            "id": 23,
            "label": "mobile",
            "number": "1233219999"
        }
    ]
}
```

Each phone number object is used to construct a row in the widget.

#### getLastRow

When adding items to a collection programmatically using `addRow` (for example, in an "update" interface), you may wish to perform additional operations on each row as it is added. To do this you may call the `getLastRow` method on your collection, and it will return the jQuery selector object for the row that was just added.

For example, suppose you wish to instantiate a an input mask on each row in the above example:

```js
// Get current phones from an AJAX source, and add to member phones
var memberId = 12;
$.getJSON(site.uri.public + '/api/members/m/' + memberId)
.done(function (data) {
    $.each(data.phones, function (idx, phone) {
        phoneCollection.ufCollection('addRow', phone);
        // Set up an input mask on the 'js-input-phone' field in the row
        phoneCollection.ufCollection('getLastRow').find('.js-input-phone').inputmask();
    });
});
```

#### addVirginRow

_Adds a "virgin" row to an existing `ufCollection` widget, optionally prepopulated with some data._

A virgin row is typically used in free-text mode, providing an intutitive way for users to create a new row in the collection without needing to explicitly click an "add" button. In free-text mode, when a virgin row is first touched by the user, it loses its "virgin" status and a new virgin row is added to the bottom of the collection. Thus, the last row in the collection will always be a virgin row.

Generally speaking, since `ufCollection` handles all of this automatically for you, you won't need to call this method explicitly.

#### deleteRow

_Delete a row_.

This method takes a jQuery selector containing the target row, and calls jQuery's `remove` method. Note that this method is automatically bound to a row's `js-delete-row` button when the row is added.

#### touchRow

_Remove a row's "virgin" status, shows the delete button (hidden for virgin rows), and add a new virgin row to the collection (if `useDropdown` is set to false)._

`ufCollection` automatically binds this method to the `focus` event for all controls in a virgin row. However, we have exposed this method in case you wish to manually invoke it or bind it to other events.

### Events

#### rowAdd.ufCollection

_Triggered when a new row is added to the collection._

This event returns the newly added row, so you can access it in your handler:

```js
$('#myCollection').on('rowAdd.ufCollection', function (event, row) {
    var phoneInput = $(row).find(".js-input-phone");
    // Apply the inputmask plugin to the phone control in the new row
    phoneInput.inputmask();
});
```

#### rowDelete.ufCollection

_Triggered when a row is removed from the collection._

#### rowTouch.ufCollection

_Triggered when any controls in a row are brought into focus._

This event returns the touched row, so you can access it in your handler.

### Options

#### useDropdown

_Set to `true` for a collection where rows are added from a dropdown (default), or `false` for free-text input mode._

#### dropdown

_An object containing options to pass to the `select2` initialization._ Unfortunately, these options are only [partially documented](http://select2.github.io/options.html) in `select2`'s documentation website. For the purposes of `ufCollection`, the salient options are:

- `ajax`: option for the AJAX request that fetches data to be loaded into the select options. The most important option is `url` but other useful options include:
    - `url`: The url to submit the AJAX GET request.
    - `cache`: Defaults to `true`, to cache AJAX results on the client side.
    - `dataType`: The type of data returned by the AJAX request. Defaults to `json`.
    - `delay`: Delay, in milliseconds, before resending the AJAX request when refreshing the select2 options during search. Defaults to `250`.
    - `data`: The callback that `select2` uses to parse the current search query (`params.term`) before submitting the AJAX request. Defaults to:

    ```js
    function (params) {
        return {
            filters: {
                info : params.term
            }
        };
    }
    ```

    With the default callback, the resulting AJAX query URL will look something like `http://example.com/api/member/12/phones?filters[info]=something`, which is the standard format expected by data Sprunjers.

    - `processResults`: The callback used to parse the data received in the response from the AJAX request.  Defaults to:

    ```js
    function (data, params) {
        var suggestions = [];
        // Process the data into dropdown options
        if (data && data.rows) {
            jQuery.each(data.rows, function(idx, row) {
                suggestions.push(row);
            });
        }
        return {
            results: suggestions
        };
    }
    ```

    Notice that this callback must always return a JSON object with a `results` key.

- `placeholder`: The placeholder to display in the dropdown before a selection is made. Note that you need to have an empty `<option>` element in your `select` control for this to work. You may alternatively use the `data-placeholder` attribute on the `select` control itself. Defaults to "Item".
- `selectOnClose`: Make a selection when the dropdown is closed for any reason (for example, even if the user clicks out of the box). Useful if your users are dumb and don't understand how select controls work. Defaults to `false`.
- `theme`: A select2 [theme](http://select2.github.io/examples.html#themes-templating-responsive-design) to apply to the dropdown. Defaults to "default".
- `width`: The width of the dropdown control relative to the parent element. Defaults to "100%".

#### dropdownControl

_A jQuery selector that indicates which element should be used as the dropdown for adding new items to the collection._

Defaults to elements with the class `.js-select-new`.

#### dropdownTemplate

See the description [above](#dropdowntemplate).

#### rowContainer

_A jQuery selector that indicates the container element to which new rows should be added._

Defaults to the first `tbody` element found in the widget container.

#### rowTemplate

See the description [above](#rowtemplate).

#### transformDropdownSelection

_A callback that transforms data from a selected dropdown item before it is passed to the Handlebars row template._

This is useful when the format of the data API that feeds your dropdown control does not match the format required by your collection row template. Simply assign to this option a callback that takes the selected item object, clones it, and returns a transformed object:

```js
transformDropdownSelection: function (item) {
    var transformed = $.extend(true, {}, item);
    transformed['project_id'] = item.id;
    transformed['id'] = null;
    return transformed;
}
```

[notice=warning]Notice that we use `$.extend(true, {}, item)` to [clone the object](https://stackoverflow.com/a/122704/2970321). If you instead were to manipulate the `item` object directly, it will change the actual object as it exists in the dropdown control, and likely break the dropdown in the process.[/notice]

#### DEBUG

_Dump debugging information to the browser console._

Defaults to `false`.
