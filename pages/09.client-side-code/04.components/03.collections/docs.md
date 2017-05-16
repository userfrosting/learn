---
title: Collections
metadata:
    description: The ufCollection widget provides a convenient interface for associating related or child entities with a single parent entity.
taxonomy:
    category: docs
---

The `ufCollection` widget provides a convenient interface for associating related or child entities with a single parent entity.  For example, you might want to associate a user account with one or more roles, or add multiple phone numbers or addresses to an employee.

![ufCollection widget as used for the "user role" management interface.](/images/uf-collection.png)

## Basic usage

To use `ufCollection`, you need to have `local/core/js/uf-collection.js` and `local/core/css/uf-collection.css` included in your page assets.  The easiest way to do this is by including the `js/form-widgets` and `css/form-widgets` [asset bundles](/building-pages/assets/asset-bundles) in your page.  Most of the default administrative pages include these bundles by default in their `stylesheets_page` and `scripts_page` Twig blocks.

Of course, feel free to include the required JS and CSS files in your page-specific asset bundles instead, if you prefer.

The basic markup for a collection widget consists of a table "skeleton" and, optionally, a `select` control for adding preexisting items to the collection.  These are wrapped together inside some sort of container element.  For example:

```
<div id="myOwls">
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

The `select` control should have the class `js-select-new`, which tells `ufCollection` that we should add a new row to the collection whenever a selection is made.  Internally `ufCollection` uses the [select2](http://select2.github.io/) library to create a beautiful, searchable dropdown menu.

You'll notice that the table contains an empty `tbody` element.  By default, `ufCollection` will add new rows to the table inside this `tbody`.  This can be overridden by setting the `rowContainer` option to some other DOM element.

To initialize the widget, simply call the `ufCollection` method on your container element:

```
$('#myOwls').ufCollection({
    dropdown: {
        ajax: {
            url: site.uri.public + '/api/owls'
        }
    },
    dropdownTemplate: $('#member-owls-select-option').html(),
    rowTemplate: $('#member-owls-row').html()
});
```

A typical use case will involve the following parameters:

### `dropdown`

This parameter contains the options that should be passed in when `ufCollection` initializes the `select2` control.  For large, dynamic collections of options, you will probably want to provide an AJAX data source with the `ajax` subkey.

`ufCollection` will expect JSON data from an AJAX source, in the same format as that returned by the [Data Sprunjer](/database/data-sprunjing).  In particular, it should have a `rows` key that contains the collection of selectable options:

```
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

### `dropdownTemplate`

`ufCollection` uses [Handlebars](http://handlebarsjs.com/), a Javascript templating engine, to render the options that will be displayed in the dropdown menu.  The `dropdownTemplate` parameter should contain a valid Handlebars template that `ufCollection` can render using the data returned by the AJAX request.

Best practices are to define your Handlebars template somewhere else in your DOM, rather than directly passing in the template as a string.  For example, in your page's Twig template, you might have a `script` tag:

```
{% verbatim %}
<script id="member-owls-select-option" type="text/x-handlebars-template">
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

We then retrieve this template using `$('#member-owls-select-option').html()` and pass this to our `ufCollection`.  For more information on defining Handlebars templates, see the section on [client-side templating](/client-side-code/client-side-templating).

### `rowTemplate`

The `rowTemplate` parameter is similar to `dropdownTemplate` but is used to render the rows for items that have been added to the collection.  For example, the Handlebars template might look like this:

```
{% verbatim %}
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
{% endverbatim %}
```

Some important features of the row template:

- The template should contain a single `tr` element with the `uf-collection-row` class.
- The last column of your row should be a "delete" button, allowing the user to remove associations.  You may style this button however you like, but it needs to have the `js-delete-row` class for `ufCollection` to bind the delete event.
- You should have a hidden `input` control in each row, that contains the `id` for the element associated with that row.
- The user can add additional information to each row via additional controls (`input`, `select`, etc).
- We recommend submitting the entire collection under a single top-level `name` attribute.  You can then use the `rownum` placeholder to index the elements of this key, and under that, the individual properties of the row.

For example, the submitted data from this collection might end up looking something like:

```
owls: {
  1: {
    species_id: 5,
    name: "Fluffers"
  },
  2: {
    species_id: 2,
    name: "Slasher"
  },
  ...
}
```

This will make it easy to parse the submitted data in PHP on the server side.

## Free-text mode

If you want to allow the client to add arbitrary rows of data, rather than preselecting them from a dropdown, you can set the `useDropdown` property of `ufCollection` to `false`.  In this case, your collection markup will look much simpler:

```
<div id="#memberPhones">
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Label</th>
                <th>Number</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
```

Notice that we no longer need to add a `select` control.  This also means that we don't need a `dropdownTemplate`.  However, we will need a `rowTemplate`:

```
{% verbatim %}
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
{% endverbatim %}
```

Initialization would then look something like:

```
$('#memberPhones').ufCollection({
    useDropdown: false,
    rowTemplate: $('#member-phones-row').html()
});
```

In free-text mode, a new empty row will automatically be added below the last row that has been "touched".  A row is "touched" whenever it is brought into focus, or when it is programmatically added using the `addRow` method.  Thus, this ensures that the user can always add another row of information without needing to click an "add" button.

![ufCollection widget as used for a collection of user's phone numbers.](/images/uf-collection-free-text.png)

## Methods, events, and options

This section covers the methods, events, and options that you can use to programmatically control your `ufCollection` widget.

### Methods

Methods are called via the standard jQuery practice of invoking the plugin name with the method name as the first argument:

`$('#myCollection').ufCollection('methodName', methodOptions);`

#### addRow

_Adds a new row to an existing `ufCollection` widget, optionally prepopulated with some data._

**Example usage:**

```
// Get current phones from an AJAX source, and add to member phones
var memberId = 12;
$.getJSON(site.uri.public + '/api/members/m/' + memberId)
.done(function (data) {
    $.each(data.phones, function (idx, phone) {
        phoneCollection.ufCollection('addRow', phone);
    });
});
```

This pattern is especially useful when you want to provide an "update" feature for a collection.  In the example above, we preload the collection with the member's current phone numbers (from an AJAX source), so that the user can add new phone numbers or remove current numbers.  In this case, we expect the data source to have a `phones` key, which contains an array of phone number objects:

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

#### addVirginRow

_Adds a "virgin" row to an existing `ufCollection` widget, optionally prepopulated with some data._

A virgin row is typically used in free-text mode, providing an intutitive way for users to create a new row in the collection without needing to explicitly click an "add" button.  In free-text mode, when a virgin row is first touched by the user, it loses its "virgin" status and a new virgin row is added to the bottom of the collection.  Thus, the last row in the collection will always be a virgin row.

Generally speaking, since `ufCollection` handles all of this automatically for you, you won't need to call this method explicitly.

#### deleteRow

_Delete a row_.

This method takes a jQuery selector containing the target row, and calls jQuery's `remove` method.  Note that this method is automatically bound to a row's `js-delete-row` button when the row is added.

#### touchRow

_Remove a row's "virgin" status, shows the delete button (hidden for virgin rows), and add a new virgin row to the collection (if `useDropdown` is set to false)._

`ufCollection` automatically binds this method to the `focus` event for all controls in a virgin row.  However, we have exposed this method in case you wish to manually invoke it or bind it to other events.

### Events

#### rowAdd.ufCollection

_Triggered when a new row is added to the collection._

This event returns the newly added row, so you can access it in your handler:

```
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

_An object containing options to pass to the `select2` initialization._  Unfortunately, these options are only [partially documented](http://select2.github.io/options.html) in `select2`'s documentation website.  For the purposes of `ufCollection`, the salient options are:

- `ajax`: option for the AJAX request that fetches data to be loaded into the select options.  The most important option is `url` but other useful options include:
    - `url`: The url to submit the AJAX GET request.
    - `cache`: Defaults to `true`, to cache AJAX results on the client side.
    - `dataType`: The type of data returned by the AJAX request.  Defaults to `json`.
    - `delay`: Delay, in milliseconds, before resending the AJAX request when refreshing the select2 options during search.  Defaults to `250`.
    - `data`: The callback that `select2` uses to parse the current search query (`params.term`) before submitting the AJAX request.  Defaults to:
    
    ```
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
    
    ```
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

- `placeholder`: The placeholder to display in the dropdown before a selection is made.  Note that you need to have an empty `<option>` element in your `select` control for this to work.  You may alternatively use the `data-placeholder` attribute on the `select` control itself.  Defaults to "Item".
- `selectOnClose`: Make a selection when the dropdown is closed for any reason (for example, even if the user clicks out of the box).  Useful if your users are dumb and don't understand how select controls work.  Defaults to `false`. 
- `theme`: A select2 [theme](http://select2.github.io/examples.html#themes-templating-responsive-design) to apply to the dropdown.  Defaults to "default".
- `width`: The width of the dropdown control relative to the parent element.  Defaults to "100%".

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

#### DEBUG

_Dump debugging information to the browser console._

Defaults to `false`.

## Server-side processing

Let's assume that your `ufCollection` widget is part of a form that you will submit to the server for processing, and that you want to update .  If you used the naming scheme for your row controls as suggested in the [section on `rowTemplate`](#rowtemplate), the collection table might end up looking something like:

```
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

This is an easy format for the server to process, because PHP will automatically convert this into a multidimensional array:

```
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

### Updating a many-to-many relationship

If the submitted data represents a [many-to-many relationship](https://laravel.com/docs/5.4/eloquent-relationships#many-to-many), Laravel provides some convenient tools to update the relationships with the parent object:

```
$owlsCollection = collect($owls)->pluck(['species_id', 'name'])->all();
$member->owls()->sync($owlsCollection);
```

Laravel's `collect` function will convert the raw multidimensional array into a collection of objects.  `pluck` will then make sure that we only grab the values we're interested in.  This is useful as a validation tool, to reject any fields that we don't want to allow the client to modify).

Finally, calling the [`sync` method](https://laravel.com/docs/5.4/eloquent-relationships#updating-many-to-many-relationships) on a member's `owls` relationship will update the entire relationship, so that the owls associated with the member match exactly the submitted data.

### Updating a one-to-many relationship

If the submitted data represents a [one-to-many relationship](https://laravel.com/docs/5.4/eloquent-relationships#one-to-many), synchronizing the database becomes a little trickier.  One approach that you might try is described [here](https://laracasts.com/discuss/channels/general-discussion/syncing-one-to-many-relationships).
