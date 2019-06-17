---
title: Adding ufTable
metadata:
    description: Adding ufTable to our page.
taxonomy:
    category: docs
---

We will now expand on the html table we [created earlier in the tutorial.](/recipes/advanced-tutorial/base-setup#the-template-file) Instead of having our basic html table code inside `templates/pages/pastries.html.twig` we will place our ufTable code inside a new Twig template:

`templates/tables/pastries.html.twig`.

```
{% extends "tables/table-paginated.html.twig" %}

{% block table %}
    <table id="{{table.id}}" class="tablesorter table table-bordered table-hover table-striped" data-sortlist="{{table.sortlist}}">
        <thead>
            <tr>
                <th data-column-name="name" data-column-template="#pastry-table-column-name" data-priority="1">{{translate('PASTRIES.NAME')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="origin" data-column-template="#pastry-table-column-origin" data-priority="1">{{translate('PASTRIES.ORIGIN')}} <i class="fa fa-sort"></i></th>
                <th data-column-name="description" data-column-template="#pastry-table-column-description" data-priority="1">{{translate('PASTRIES.DESCRIPTION')}} <i class="fa fa-sort"></i></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
{% endblock %}

```

Your ufTable should extends `tables/table-paginated.html.twig` and the table structure is inside the `{% block table %}`. Each of our table columns has the `data-column-template` data-* attribute set, which will be used by Handlebars. The required `data-*` attributes can be found in the [chapter on ufTable](http://learn.test/client-side-code/components/tables#column-headers).

### Handlebars

Next, we will add in our Handlebars code. Add in the additional block to `/tables/pastries.html.twig`. Notice the code inside this block is wrapped inside the `{% verbatim %}` tags.

```
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
    {% endverbatim %}
```



### Add an asset

We will need to add an asset so that ufTable can dynamically fetch data from the database. Assets can be 'bundled' by including them in `asset-bundles.json` in the root directory of your sprinkle.

Create the directory `assets` and sub directory `js` inside `pastries`. Then create `pages` and `widgets` sub directories inside `js`:
```
pastries
├──assets
   ├── js
       ├── pages
       ├── widgets
```




### Adding an asset-bundle

`asset-bundle.json` is stored in the root directory of your sprinkle. Create that file now:

`pastries/asset-bundle.json`
```
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


### Update the page template files

Now that we have a dedicated Twig template file for our table we can go back and modify `/pages/pastries.html.twig`. We will replace the basic html table code with our ufTable Twig template file using `include`:

```
<div class="box-body">
    {% include "tables/pastries.html.twig" with {
            "table" : {
                "id" : "table-pastries"
            }
        }
    %}
</div>
```

In `tables/pastries.html.twig` we set `id="{{table.id}}"`. This allows us to set this value when we `include` the Twig file. We have set the `id` to `table-pastries`.

There are a few more things we will add to setup for additions further on in the tutorial.

`pages/pastries.html.twig`

```
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
