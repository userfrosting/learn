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
