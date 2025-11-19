---
title: Client-side Templating
metadata:
    description: An overview of how UserFrosting uses Handlebars.js for client-side templating.
taxonomy:
    category: docs
---
<!-- [plugin:content-inject](/modular/_update5.0) -->

In [Templating with Twig](/templating-with-twig), we learned how Twig helps you separate the logic of your server-side application from the layout of the pages it generates. Handlebars.js plays a similar role on the client side of your application, in Javascript. The main difference is that with Twig we are often generating complete pages, whereas with Handlebars we typical only generate smaller snippets of HTML to be inserted into the DOM.

## Handlebars.js basic usage

Handlebars is fairly straightforward to use. A Handlebars template is created by calling `Handlebars.compile` on a string:

```js
var counterTemplate = Handlebars.compile("Owls in my care: <span class=\"pull-right badge bg-blue\">{{owls.count}}</span>");
```

We can then render this template, calling the template on a JSON object and appending the rendered template to an element in our page's DOM:

```js
var counterRendered = counterTemplate({
    owls: {
        count: 5
    }
});
var counterDOM = $(counterRendered).appendTo($("#parentDiv"));
```

Of course, the JSON object is optional and can be omitted if your template does not contain any dynamically generated content.

### Template blocks

Since writing long HTML snippets as strings can become unwieldy and difficult to read, we can place our templates in `script` tags with the `text/x-handlebars-template` type attribute. The entire `script` block can then be rendered in our page's Twig template:

```html

{# This contains a series of <script> blocks, each of which is a client-side Handlebars template.
 # Note that these are NOT Twig templates, although the syntax is similar.
 #
 # These templates require handlebars-helpers.js.
#}

{% verbatim %}
<script id="owl-description-item" type="text/x-handlebars-template">
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

By placing each Handlebars template in a separate `script` tag, and giving each one a unique `id`, we make it easy to choose a template to compile and render in our Javascript code:

```js
var owlTemplate = Handlebars.compile($("#owl-description-item").html());
```

Note that since Handlebars and Twig have similar syntax, we wrap our Handlebars templates in Twig's `verbatim` tag so that Twig won't try to parse the Handlebars template when it renders the page.

## Template syntax

Handlebars.js and Twig use a similar syntax. As with Twig, placeholders are represented using the `{{double-mustache}}` notation. However, compared to Twig, Handlebars.js is fairly sparse in terms of control structures and other features.

### If blocks

In Handlebars, `#if` is a **helper**. Helpers always begin with `#` in their opening tag, and `/` in their closing tag.

```html
<div class="entry">
  {{#if author}}
    <h1>{{firstName}} {{lastName}}</h1>
  {{else}}
    <h1>Unknown Author</h1>
  {{/if}}
</div>
```

It's also important to note that the `if` helper in Handlebars doesn't support logical expressions. For example:

```html
<!-- won't work! -->
{{#if author == "Attenborough"}}
  <h1>{{firstName}} {{lastName}}</h1>
{{/if}}
```

To compare two values in an if/else block, use our custom Handlebars helper instead:

```html
{{#ifx author '==' 'David Attenborough' }}
  <h1>{{firstName}} {{lastName}}</h1>
{{/ifx}}
```

[notice=info]`#ifx` supports the basic logical operators (`==`, `!=`, `>`, `<`, etc), but does not support compound expressions. You can instead nest your expressions, or create your own custom helper. For more information, see [this Stack Overflow question](http://stackoverflow.com/questions/8853396/logical-operator-in-a-handlebars-js-if-conditional).[/notice]

### Loops

Handlebars.js provides a limited syntax for loops using the `#each` helper:

If you pass this object:

```js
var data = {
  owls: [
    "Fluffers",
    "Slasher",
    "Muhammad Owli"
  ]
}
```

...when rendering this template:

```html
<ul id="myOwls">
  {{#each owls}}
    <li>{{this}}</li>
  {{/each}}
</ul>
```

Handlebars will generate the `li`s with the values from the `owls` list. In an `#each` block, `this` refers to the current value in our iteration over the array.

In the real world, it's actually **fairly rare** that you will end up using the `#each` helper. More likely, you will end up with a template that renders just a **single element** of a list or row in a table. You'll then render each row using a jQuery loop:

**Template:**

```html
{# "Parent" element that will hold your list of owls #}
<ul id="myOwls">
</ul>

{# Handlebars template for generating the list items in myOwls #}
{% verbatim %}
<script id="owls-list-item" type="text/x-handlebars-template">
    <li>{{name}}</li>
</script>
{% endverbatim %}
```

Your page Javascript might have something like:

```js
// Compile the template
var owlItemTemplate = Handlebars.compile($("#owls-list-item").html());

// Loop through the owls, rending each item individually and then insert in the parent element
$.each(data.owls, function (idx, owl) {
    var owlItemRendered = owlItemTemplate(owl);
    $(owlItemRendered).appendTo($("#myOwls"));
});
```

This approach is very common in client side templating, because users often need to dynamically interact with collections of elements. Having a template for a single row or item allows you to dynamically add and remove individual items in your client-side application.

## Other built-in helpers

Handlebars' full list of built-in helpers can be found [here](http://handlebarsjs.com/guide/builtin-helpers.html#if).

## Extra helpers

UserFrosting provides some additional helpers on top of Handlebars' built-in ones:

### dateFormat

Format an ISO date using [Moment.js](http://momentjs.com).

```
{{name}} was adopted on {{dateFormat adoption_date format="MMMM YYYY"}}.
```

`format` should be a valid Moment.js [format string](https://momentjs.com/docs/#/displaying/format/).

### phoneUSFormat

Format a string as a US phone number (`(xxx) xxx-xxxx`)

### currencyUsdFormat

Format a floating-point value as US currency.
