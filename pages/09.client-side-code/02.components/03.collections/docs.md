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

If you would like to use an alternative format for your source data, you can override the `dropdown.ajax.processResults` callback option:

```
processResults: function (data, params) {
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

### `dropdownTemplate`

`ufCollection` uses [Handlebars](http://handlebarsjs.com/), a Javascript templating engine, to render the options that will be displayed in the dropdown menu.  The `dropdownTemplate` parameter should contain a valid Handlebars template that `ufCollection` can render using the data returned by the AJAX request.

Best practices are to define your Handlebars template somewhere else in your DOM, rather than directly passing in the template as a string.  For example, in your page's Twig template, you might have a `script` tag:

```
{# This contains a series of <script> blocks, each of which is a client-side Handlebars template.
 # Note that these are NOT Twig templates, although the syntax is similar.  We wrap them in the `verbatim` tag,
 # so that Twig will output them directly into the DOM instead of trying to treat them like Twig templates.
 #
 # These templates require handlebars-helpers.js, moment.js
#}

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

Notice that we use the `type="text/x-handlebars-template"` attribute, which tells the browser that the `script` tag contains a Handlebars template rather than executable Javascript.  The syntax of the Handlebars template is very similar to Twig's, but don't be fooled!  The syntax for control structures (e.g. `if`, `for`) is very different in Handlebars.  You should consult their documentation for more help with this.

We then retrieve the contents of the `script` tag using `$('#member-owls-select-option').html()` and pass this to our `ufCollection`.

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
                <input type="text" class="form-control" name="phones[{{ rownum }}][label]" value="{{label}}" placeholder="Label">
            </div>
        </td>
        <td>
            <input type="text" class="form-control" name="phones[{{ rownum }}][number]" value="{{phone}}" placeholder="Number">
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

Coming soon!
