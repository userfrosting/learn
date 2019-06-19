---
title: Adding ufTable
metadata:
    description: Adding ufTable to our page.
taxonomy:
    category: docs
---

### Twig template file

Rather than using the basic html table that was [created earlier in the tutorial](/recipes/advanced-tutorial/base-setup#the-template-file) inside `/pages/pastries.html.twig`, the ufTable code will be placed into a separate file inside the `tables` sub directory. We will take a look at the complete file code and then go back through and look at a few different chunks of code.

`templates/tables/pastries.html.twig`.

```html
{% extends "tables/table-paginated.html.twig" %}

{% block table %}
    <table id="{{table.id}}" class="tablesorter table table-bordered table-hover table-striped" data-sortlist="{{table.sortlist}}">
        <thead>
            <tr>
                <th data-column-name="name" data-column-template="#pastry-table-column-name" data-priority="1">{{translate('PASTRIES.NAME')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="origin" data-column-template="#pastry-table-column-origin" data-priority="1">{{translate('PASTRIES.ORIGIN')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="description" data-column-template="#pastry-table-column-description" data-priority="1">{{translate('PASTRIES.DESCRIPTION')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="actions" data-column-template="#pastry-table-column-actions" data-priority="1" data-sorter="false" data-filter="false">{{translate('ACTIONS')}} </th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
{% endblock %}

{% block table_cell_templates %}
    {% verbatim %}
    <script id="pastry-table-column-name" type="text/x-handlebars-template">
        <td>
            {{row.name}}
        </td>
    </script>
    <script id="pastry-table-column-origin" type="text/x-handlebars-template">
        <td>
            {{row.origin}}
        </td>
    </script>
    <script id="pastry-table-column-description" type="text/x-handlebars-template">
        <td>
            {{row.description}}
        </td>
    </script>
    <script id="pastry-table-column-actions" type="text/x-handlebars-template">
        <td>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    {% endverbatim %}{{translate("ACTIONS")}}{% verbatim %}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li>
                        <a href="#" data-name="{{row.name}}" class="js-pastry-edit">
                        <i class="fa fa-edit"></i> {% endverbatim %}{{translate('PASTRIES.EDIT')}}{% verbatim %}
                        </a>
                    </li>
                    <li>
                        <a href="#" data-name="{{row.name}}" class="js-pastry-delete">
                        <i class="fa fa-trash-o"></i> {% endverbatim %}{{translate('PASTRIES.DELETE')}}{% verbatim %}
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </script>
    {% endverbatim %}
{% endblock %}

```

#### block table

The table structure is inside the `{% block table %}` block. Each of our table column headers has the `data-column-template` data-* attribute set, which will be used by Handlebars. The required `data-*` attributes can be found in the [chapter on ufTable](client-side-code/components/tables#column-headers). Notice we have added an additional column header for `actions` which will be used for a drop-down `actions` menu for each row, with `Edit` and `Delete` buttons.

```html
{% block table %}
    <table id="{{table.id}}" class="tablesorter table table-bordered table-hover table-striped" data-sortlist="{{table.sortlist}}">
        <thead>
            <tr>
                <th data-column-name="name" data-column-template="#pastry-table-column-name" data-priority="1">{{translate('PASTRIES.NAME')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="origin" data-column-template="#pastry-table-column-origin" data-priority="1">{{translate('PASTRIES.ORIGIN')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="description" data-column-template="#pastry-table-column-description" data-priority="1">{{translate('PASTRIES.DESCRIPTION')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="actions" data-column-template="#pastry-table-column-actions" data-priority="1" data-sorter="false" data-filter="false">{{translate('ACTIONS')}} </th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
{% endblock %}
```

#### block table_cell_templates

Notice that the `id`s in the template `<script>` tags match the `data-column-template` values that were set in the `block table` block. Noticed that the script for `pastry-table-column-actions` includes a `btn-group` with two buttons - `edit` and `delete`.

```html
{% block table_cell_templates %}
    {% verbatim %}
    <script id="pastry-table-column-name" type="text/x-handlebars-template">
        <td>
            {{row.name}}
        </td>
    </script>
    <script id="pastry-table-column-origin" type="text/x-handlebars-template">
        <td>
            {{row.origin}}
        </td>
    </script>
    <script id="pastry-table-column-description" type="text/x-handlebars-template">
        <td>
            {{row.description}}
        </td>
    </script>
    <script id="pastry-table-column-actions" type="text/x-handlebars-template">
        <td>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    {% endverbatim %}{{translate("ACTIONS")}}{% verbatim %}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li>
                        <a href="#" data-name="{{row.name}}" class="js-pastry-edit">
                        <i class="fa fa-edit"></i> {% endverbatim %}{{translate('PASTRIES.EDIT')}}{% verbatim %}
                        </a>
                    </li>
                    <li>
                        <a href="#" data-name="{{row.name}}" class="js-pastry-delete">
                        <i class="fa fa-trash-o"></i> {% endverbatim %}{{translate('PASTRIES.DELETE')}}{% verbatim %}
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </script>
    {% endverbatim %}
{% endblock %}
```

### Assets

We will need to add an asset so that ufTable can dynamically fetch data from the database.

Create the directory `assets` and sub directory `js` inside `pastries`. Then create `pages` and `widgets` sub directories inside `js`:

```
pastries
├──assets
   ├── js
       ├──pages
          └── pastries.js
       └──tables
          └── pastries.js
```

#### Adding assets to the asset-bundle

Assets can be bundled by including them in `asset-bundles.json` in the root directory of your sprinkle. Create that file now:

`pastries/asset-bundle.json`

```json
{
  "bundle": {
    "js/pages/pastries": {
      "scripts": [
        "js/widgets/pastries.js",
        "js/pages/pastries.js"
      ],
      "options": {
        "result": {
          "type": {
            "scripts": "plain"
          }
        }
      }
    }
  }
}
```

Here are some things to take note of:

- `js/pages/pastries` (without the `.js`) is the name of our asset-bundle and is what will be referenced in our Twig template when we add the asset-bundle to the page.

- This asset-bundle includes two assets (the files we created in the previous step): `js/widgets/pastries.js` and `js/pages/pastries.js`.



### Updating `pages/pastries.html.twig`

Now that we have a dedicated Twig template file for our table we can go back and modify `/pages/pastries.html.twig` to include that Twig template file inside of it.

`pages/pastries.html.twig`

```html
{% extends "pages/abstract/dashboard.html.twig" %}

{# Overrides blocks in head of base template #}
{% block page_title %}{{translate('PASTRIES')}}{% endblock %}
{% block page_description %}{{translate('PASTRIES.PAGE')}}{% endblock %}

{% block body_matter %}
    <div class="row">
        <div class="col-md-12">
                  <div id="widget-pastries" class="box box-primary">
                      <div class="box-header">
                          <h3 class="box-title"><i class="fa fa-cutlery fa-fw"></i> {{translate('PASTRIES.LIST')}}</h3>
                          {% include "tables/table-tool-menu.html.twig" %}
                      </div>
                      <div class="box-body">
                          {% include "tables/pastries.html.twig" with {
                                  "table" : {
                                      "id" : "table-pastries"
                                  }
                              }
                          %}
                      </div>
                      <div class="box-footer">
                          <button type="button" class="btn btn-success js-pastry-create">
                              <i class="fa fa-plus-square"></i>  {{translate('PASTRIES.CREATE')}}
                          </button>
                      </div>
                  </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block scripts_page %}
    <!-- Include form widgets JS -->
    {{ assets.js('js/form-widgets') | raw }}

    <!-- Include page-specific JS -->
    {{ assets.js('js/pages/pastries') | raw }}
{% endblock %}
```

#### Including `tables/pastries.html.twig`

In `tables/pastries.html.twig` we set the table `id` to `{{table.id}}`, which allows us to set the table the value when we `include` the Twig file. We have set the `id` to `table-pastries`.

```html
<div class="box-body">
    {% include "tables/pastries.html.twig" with {
            "table" : {
                "id" : "table-pastries"
            }
        }
    %}
</div>
```

#### Create button

We have added a button for the create action at the bottom of the page.

```html
<div class="box-footer">
    <button type="button" class="btn btn-success js-pastry-create">
        <i class="fa fa-plus-square"></i>  {{translate('PASTRIES.CREATE')}}
    </button>
</div>
```

#### Including assets

Two assets have been included on this page.

```html
{% block scripts_page %}
    <!-- Include form widgets JS -->
    {{ assets.js('js/form-widgets') | raw }}

    <!-- Include page-specific JS -->
    {{ assets.js('js/pages/pastries') | raw }}
{% endblock %}
```

### Asset files

We have now created and added to our page an asset-bundle that contains two assets that don't yet exist. Let's go ahead and create those two files now.

`assets/js/pages/pastries.js`

```js
$(document).ready(function() {

    $("#widget-pastries").ufTable({
        dataUrl: site.uri.public + "/api/pastries",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind creation button
    bindPastryCreationButton($("#widget-pastries"));

    // Bind table buttons
    $("#widget-pastries").on("pagerComplete.ufTable", function() {
        bindPastriesTableButtons($(this));
    });
});
```

`assets/js/widgets/pastries.js`

```js
/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachPastryForm() {
    $("body").on('renderSuccess.ufModal', function(data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        // Set up any widgets inside the modal
        form.find(".js-select2").select2({
            width: '100%'
        });

        // Set up the form for submission
        form.ufForm({
            validator: page.validators
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}


function bindPastriesTableButtons(el, options) {
    if (!options) options = {};

    /**
     * Buttons that launch a modal dialog
     */
    // Edit pastry details button
    el.find('.js-pastry-edit').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/pastries/edit",
            ajaxParams: {
                name: $(this).data('name')
            },
            msgTarget: $("#alerts-page")
        });

        attachPastryForm();
    });

    // Delete pastry button
    el.find('.js-pastry-delete').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/pastries/confirm-delete",
            ajaxParams: {
                name: $(this).data('name')
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function() {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
                .on("submitSuccess.ufForm", function() {
                    // Navigate or reload page on success
                    if (options.delete_redirect) window.location.href = options.delete_redirect;
                    else window.location.reload();
                });
        });
    });

}

function bindPastryCreationButton(el) {
    // Link create button
    el.find('.js-pastry-create').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/pastries/create",
            msgTarget: $("#alerts-page")
        });

        attachPastryForm();
    });
};
```
