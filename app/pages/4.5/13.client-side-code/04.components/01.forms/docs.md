---
title: Forms
metadata:
    description: The ufForm widget makes it easy to set up simple forms for validation and AJAX submission.
taxonomy:
    category: docs
---

You may have noticed that in UserFrosting, forms are usually submitted via an AJAX request. By submitting forms with AJAX rather than HTML's native form submission, we can control the behavior of the page before submission (client-side validation, transforming form data) and after submission (deciding whether to reload the page, redirect, display messages, etc).

UserFrosting's `ufForm` widget makes it easy to handle many form-submission tasks automatically. Simply create your usual form markup:

```html
<form id="sign-in" action="{{site.uri.public}}/account/login" method="post">
  {% include "forms/csrf.html.twig" %}
  <div class="form-group has-feedback">
    <label>Username</label>
    <input type="text" class="form-control" name="user_name">
  </div>
  <div class="form-group has-feedback">
    <label>Password</label>
    <input type="password" class="form-control" name="password">
  </div>
  <div class="row">
    <div class="col-xs-8">
      <div class="checkbox icheck">
        <label>
          <input type="checkbox" class="js-icheck" name="rememberme"> Remember me
        </label>
      </div>
    </div>
    <div class="col-xs-4">
      <button type="submit" class="btn btn-primary btn-block btn-flat">Sign in</button>
    </div>
  </div>
</form>
```

You can then initialize the `ufForm` widget on the `form` element in your page Javascript:

```js
$("#sign-in").ufForm({
    validators: page.validators.login,
    msgTarget: $("#alerts-page")
}).on("submitSuccess.ufForm", function(event, data, textStatus, jqXHR) {
    redirectOnLogin(jqXHR);
});
```

The form will automatically be validated and submitted when the user clicks the `submit` button (`<button type="submit"...>`). The submit button must be _inside_ the `<form>` block for this to work properly.

## Options

### reqParams

This option contains any AJAX settings that you would like to pass through to the form submission [`.ajax` handler](http://api.jquery.com/jquery.ajax/#jQuery-ajax-settings).

The defaults for this option should be sufficient most of the time - you shouldn't need to change them in most circumstances. By default, `ufForm` will determine the request url and HTTP verb from your `<form>` tag's `action` and `method` attributes, respectively.

### validators

If the `validators` option is set, `ufForm` will automatically validate on the form using the jQueryValidation plugin with the rules provided. `validators` should be a JSON object, which might look something like:

```json
{
    "rules": {
        "user_name": {
            "required": true,
            "noLeadingWhitespace": true,
            "noTrailingWhitespace": true
        },
        "password": {
            "required": true
        },
        "rememberme": []
    },
    "messages": {
        "user_name": {
            "required": "Please specify a value for 'Username'.",
            "noLeadingWhitespace": "The value for 'Username' cannot begin with spaces, tabs, or other whitespace.",
            "noTrailingWhitespace": "The value for 'Username' cannot end with spaces, tabs, or other whitespace."
        },
        "password": {
            "required": "Please specify a value for 'Password'."
        }
    }
}
```

See the section on [generating client-side validation rules in Fortress](/routes-and-controllers/client-input/validation#generating-client-side-rules) to see how `page.validators.login` can be automatically generated from a Fortress schema.

### msgTarget

If `ufForm` receives an error from the server when it attempts to submit your form (i.e., the response contains a 4xx or 5xx status code), it will automatically retrieve any error messages from the [alert stream](/routes-and-controllers/alert-stream) and render them on the page. `msgTarget` allows you to specify an element of the DOM where `ufForm` should display these messages.

Internally, `ufForm` will set up a `ufAlerts` widget to fetch and render the alert stream messages.

If `msgTarget` is not specified, `ufForm` will use the first element inside your form with the `js-form-alerts` class by default.

### encType

The encoding type to use for submitting the form. By default, `ufForm` will use the `enctype` attribute in your `<form>` tag.

If the attribute is not set in the form or this option, the encoding type will default to `application/x-www-form-urlencoded`. The default will work for most types of forms. The exception is for forms that involve file uploads. In this case, you need to use `enctype = "multipart/form-data"` instead.

### submittingText

Content to show in the submit button (`<button type="submit"...>`) while the form is being submitted. Defaults to a spinner icon: `"<i class='fa fa-spinner fa-spin'></i>"`. When the submission button is clicked, the button will become disabled and this content will be displayed while the request is being submitted. Once the form submission is complete and a response has been received, `ufForm` automatically re-enables the button and reverts its text back to its original value.

### beforeSubmitCallback

An optional callback function to execute after the form has been validated successfully, but before submitting the AJAX request.

### binaryCheckboxes

If this option is set to `true`, `ufForm` will ignore the `value` attributes of any [`checkbox` controls](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/checkbox#value) and instead transform the values of the checkboxes to binary 0/1 values.

For example, suppose you have the checkbox control:

```html
<input type="checkbox" name="rememberme" value="yes">
```

When the checkbox is checked, the submitted data will look something like `rememberme=yes`. But if it's _not_ checked, then no `rememberme` value will be submitted to the server at all!

This behavior can be frustrating when processing the form on the server side. Unlike other types of controls, where a missing value might be considered a validation error, the interpretation of a missing checkbox can be ambiguous. When using a single checkbox, the purpose is typically to serve as a "toggle" element representing true/false. But of course since an unchecked box is not submitted at all, you would need the server to provide a default "false" value.

To summarize, when `binaryCheckboxes` is set to `false`:

| Checkbox status | Submitted data   |
| --------------- | ---------------- |
| checked         | `rememberme=yes` |
| unchecked       | -                |

By setting `binaryCheckboxes` to `true` (the default), we get a much more predictable behavior from our checkbox:

| Checkbox status | Submitted data |
| --------------- | -------------- |
| checked         | `rememberme=1` |
| unchecked       | `rememberme=0` |

### keyupDelay

The time, in milliseconds, to wait before revalidating the form after the user stops typing. See [this SO question](http://stackoverflow.com/questions/41363409/jquery-validate-add-delay-to-keyup-validation) for more information.

Defaults to `0`.

## Events

### submitSuccess.ufForm

Triggered when the form has been submitted successfully. This happens after the submission button has been re-enabled.

Any response from the server will be provided in the `data` parameter as JSON. For example, to log the response from the server when the form has been submitted successfully :

```js
$("#account-settings").ufForm({
    validator: page.validators.account_settings,
    msgTarget: $("#alerts-page")
}).on("submitSuccess.ufForm", function(event, data, textStatus, jqXHR) {
    // Log data to console
    console.log(data);
});
```

### submitError.ufForm

Triggered when the form submission has returned an error. This happens after the submission button has been re-enabled and any error messages have been rendered.
