---
title: Tables
metadata:
    description: ufTable is a wrapper for Mottie's tablesorter plugin that automatically fetches JSON data from a specified API endpoint, and dynamically builds paginated, sorted, filtered views on the fly.
taxonomy:
    category: docs
---

A typical application feature is to display a table of entities from a server-side data source (e.g., a database).  For example, the `admin` Sprinkle generates client-side tables for admins to view and manage users, groups, roles, activities, and permissions:

![Viewing a table of user activities](/images/table-activities.png)

`ufTable` provides a convenient way to generate sortable, searchable, paginated tables of data from an AJAX source using Mottie's [tablesorter](https://mottie.github.io/tablesorter/docs/) jQuery plugin.

## Usage

A typical use case is to create a "skeleton" `<table>` in your Twig template, and then use `ufTable` to dynamically retrieve data from a JSON data source and construct the rows.  As the user sorts columns, inputs filter queries, and pages through the data, `ufTable` will submit new AJAX requests to the server and refresh the `<table>` with the results of the updated queries.

For example, consider the Users table.  First, we create a partial template that extends the base `components/tables/table-paginated.html.twig` template (we can include this partial template in a page template using the `include` tag later):

**components/tables/users-custom.html.twig**:

```twig
{% extends "components/tables/table-paginated.html.twig" %}

{% block table %}
    <table id="{{table.id}}" class="tablesorter table table-bordered table-hover table-striped" data-sortlist="{{table.sortlist}}">
        <thead>
            <tr>
                <th class="sorter-metatext" data-column-name="name" data-column-template="#user-table-column-info">User info <i class="fa fa-sort"></i></th>
                <th class="sorter-metanum" data-column-name="last_activity" data-column-template="#user-table-column-last-activity">Last activity <i class="fa fa-sort"></i></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
{% endblock %}
```

Your table skeleton should be defined in the `table` block as a `<table>` element.  It should have a unique `id` attribute, and the `tablesorter` class (`ufTable` uses this class internally to reference the table).  The other classes on `<table>` are [styling classes from Bootstrap](http://getbootstrap.com/css/#tables) and are optional.  The `data-sortlist` attribute is a [tablesorter setting](https://mottie.github.io/tablesorter/docs/example-option-sort-list.html) that tells tablesorter how to initially sort the table when the page is loaded.

You'll notice that we populated the table with all of its column headers, but an empty `tbody` element.  This empty `tbody` is where `ufTable` will automatically render the rows using data from the AJAX source.

Each `th` element has a `data-column-name` attribute and a `data-column-template` attribute.

`data-column-name` is used to determine the [Sprunje filter](/database/data-sprunjing#custom-sorts-and-filters) name to use when the user types a query into the filter box for that column.  For example, if we type "userfrost" into the filter box in the "User info" column:

![Searching a table](/images/uf-table-search.png)

`ufTable` will add a query parameter `filters[name]=userfrost` in the next AJAX request it makes.  See [Data Sprunjing](/database/data-sprunjing) for information on setting up a Sprunjed data source in your server-side code.

`data-column-template` is an identifier used to find the [Handlebars template](/client-side-code/client-side-templating) to use when rendering the cells for that particular column.  For this example, we will define two Handlebars templates in the `table_cell_templates` block of our Twig template:

```twig
{% block table_cell_templates %}

    {% verbatim %}
    <script id="user-table-column-info" type="text/x-handlebars-template">
        <td data-text="{{row.last_name}}">
            <strong>
                <a href="{{site.uri.public}}/admin/users/u/{{row.user_name}}">{{row.first_name}} {{row.last_name}} ({{row.user_name}})</a>
            </strong>
            {{row.email}}
        </td>
    </script>

    <script id="user-table-column-last-activity" type="text/x-handlebars-template">
        {{#if row.last_activity }}
        <td data-num="{{dateFormat row.last_activity.occurred_at format='x'}}">
            {{dateFormat row.last_activity.occurred_at format="dddd"}}<br>{{dateFormat row.last_activity.occurred_at format="MMM Do, YYYY h:mm a"}}
            <br>
            <i>{{row.last_activity.description}}</i>
        </td>
        {{ else }}
        <td data-num="0">
            <i>Unknown</i>
        </td>
        {{/if }}
    </script>
    
    ...
    {% endverbatim %}

{% endblock %}
```

Notice that the `ids` in these template `<script>` tags match the `data-column-template` attributes of your table skeleton.

You'll also notice that we have custom `data-*` attributes in the `<td>` tags of each of these templates.  These refer to tablesorter's [custom sort parsers](https://mottie.github.io/tablesorter/docs/example-parsers-advanced.html), which lets us define a custom parameter for tablesorter to use when sorting the table by the corresponding column.  We will discuss this more later.

To use your table, simply `include` your table partial template in your page inside a wrapper `<div>`:

```twig
<div id="myUserTable">
    <button class="btn btn-sm btn-default js-uf-table-download"><i class="fa fa-table"></i> Download CSV</button>
    {% include "components/tables/users-custom.html.twig" %}
</div>
```

If you create a button with the `.js-uf-table-download` class in your wrapper as well, it will be automatically bound to trigger an AJAX request for downloading the table in CSV format.

In your page Javascript, initialize `ufTable` on your wrapper element:

```js
$("#myUserTable").ufTable(options);
```

Where `options` is a JSON object containing the configuration options for your table.

## Options

### dataUrl

The absolute url for the table's AJAX data source.  `ufTable` expects the data source to use the Sprunje API.  Thus, it should be able to understand the API for [Sprunje requests](/database/data-sprunjing#sprunje-parameters), and return data in the Sprunje response format:

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

### msgTarget

If `ufTable` receives an error from the server when it attempts to retrieve row data, it will automatically retrieve any error messages from the [alert stream](/routes-and-controllers/alert-stream) and render them on the page.  `msgTarget` allows you to specify an element of the DOM where `ufTable` should display these messages.

Internally, `ufTable` will set up a `ufAlerts` widget to fetch and render the alert stream messages.

If `msgTarget` is not specified, `ufTable` will look for an element on the page with an `id` of `#alerts-page` by default.

### tablesorter

An object containing tablesorter's [configuration options](https://mottie.github.io/tablesorter/docs/#Configuration).  The default values for this object are:

```json
{
    debug: false,
    theme     : 'bootstrap',
    widthFixed: true,
    // See https://mottie.github.io/tablesorter/docs/example-pager-ajax.html
    widgets: ['saveSort', 'sort2Hash', 'filter', 'pager', 'columnSelector', 'reflow2'],
    widgetOptions : {
        columnSelector_layout : '<label><input type="checkbox"> <span>{name}</span></label>',
        filter_cssFilter: 'form-control',
        filter_saveFilters : true,
        filter_serversideFiltering : true,
        filter_selectSource : {
            '.filter-select' : function() { return null; }
        },

        // apply disabled classname to the pager arrows when the rows at either extreme is visible
        pager_updateArrows: true,

        // starting page of the pager (zero based index)
        pager_startPage: 0,

        // Number of visible rows
        pager_size: 10,

        // Save pager page & size if the storage script is loaded (requires $.tablesorter.storage in jquery.tablesorter.widgets.js)
        pager_savePages: true,

        // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
        // table row set to a height to compensate; default is false
        pager_fixedHeight: false,

        // remove rows from the table to speed up the sort of large tables.
        // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
        pager_removeRows: false, // removing rows in larger tables speeds up the sort

        // target the pager markup - see the HTML block below
        pager_css: {
            errorRow    : 'uf-table-error-row', // error information row
            disabled    : 'disabled' // Note there is no period "." in front of this class name
        },

        // Must be initialized with a 'data' key
        pager_ajaxObject: {
            data: {},
            dataType: 'json'
        },
        // jQuery selectors
        pager_selectors: {
          container   : '.pager',       // target the pager markup (wrapper)
          first       : '.first',       // go to first page arrow
          prev        : '.prev',        // previous page arrow
          next        : '.next',        // next page arrow
          last        : '.last',        // go to last page arrow
          gotoPage    : '.gotoPage',    // go to page selector - select dropdown that sets the current page
          pageDisplay : '.pagedisplay', // location of where the "output" is displayed
          pageSize    : '.pagesize'     // page size selector - select dropdown that sets the "size" option
        },

        // hash prefix
        sort2Hash_hash              : '#',
        // don't '#' or '=' here
        sort2Hash_separator         : '|',
        // this option > table ID > table index on page
        sort2Hash_tableId           : null,
        // if true, show header cell text instead of a zero-based column index
        sort2Hash_headerTextAttr    : 'data-column-name',
        // direction text shown in the URL e.g. [ 'asc', 'desc' ]
        sort2Hash_directionText     : [ 'asc', 'desc' ], // default values
        // if true, override saveSort widget sort, if used & stored sort is available
        sort2Hash_overrideSaveSort  : true, // default = false
    }
}
```

### addParams

An object containing any additional key-value pairs that you want appended to the AJAX requests made by the table.  Useful when implementing, for example, site-wide filters or using data sources that require additional context.

## Events

`ufTable` triggers the following events:

### pagerComplete.ufTable

Triggered when the tablesorter pager plugin has completed rendering of the table.

Of course, you can always bind handlers directly to [tablesorter's events](https://mottie.github.io/tablesorter/docs/#events) as well.

## Methods

### getTableStateVars(table)

Fetches the current page size, page number, sort order, sort field, and column filters.

## Customizing the base template for your table

If you don't want to use the default `table-paginated.html.twig` base template for your tables, you can create your own base template.  Your template needs to have three things:

- `{% block table %}`: This is the Twig block where the table skeleton will go.
- `{% block table_cell_templates %}`: This is the Twig block where cell templates will be placed.
- `{% block table_info %}`: This is a container for displaying alternative messages, such as "no records found".  The container element should have the `js-uf-table-info` class.
- `{% block table_pager_controls %}`: A container for navigation controls for your table's pagination.  The container element should have the `js-uf-table-pager` class.

Your base template might end up looking something like:

```
{% block table %}
    {# Define your table skeleton in this block in your child template #}
{% endblock %}

{% block table_cell_templates %}
    {# Define your Handlebars cell templates in this block in your child template #}
{% endblock %}

{% block table_info %}
    <div class="uf-table-info js-uf-table-info" data-message-empty-rows="{{translate('NO_RESULTS')}}">
    </div>
{% endblock %}

{% block table_pager_controls %}
    <div class="pager pager-lg tablesorter-pager js-uf-table-pager">
        <span class="pager-control first" title="First page"><i class="fa fa-angle-double-left"></i></span>
        <span class="pager-control prev" title="Previous page"><i class="fa fa-angle-left"></i></span>
        <span class="pagedisplay"></span> {# this can be any element, including an input #}
        <span class="pager-control next" title="Next page"><i class="fa fa-angle-right"></i></span>
        <span class="pager-control last" title= "Last page"><i class="fa fa-angle-double-right"></i></span>
        <br><br>
        {{translate("PAGINATION.GOTO")}}: <select class="gotoPage"></select> &bull; {{translate("PAGINATION.SHOW")}}:
        <select class="pagesize">
        {% for count in pager.take|default([5, 10, 50, 100]) %}
            <option value="{{count}}">{{count}}</option>
        {% endfor %}
        </select>
    </div>
{% endblock %}
```
