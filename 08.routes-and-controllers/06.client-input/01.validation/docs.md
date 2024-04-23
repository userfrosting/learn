---
title: Validation
metadata:
    description: Client- and server-side validation are unified into one convenient interface using UserFrosting's Fortress package and a common set of rules defined in a JSON schema file.
taxonomy:
    category: docs
---

The number one security rule in web development is: **never trust client input!**

Data from the outside world is the Achilles' heel of modern interactive web services and applications. Code injection, cross-site scripting (XSS), CSRF, and many other types of malicious attacks are successful when a web application accepts user input that it shouldn't have, or fails to neutralize the damaging parts of the input. Even non-malicious users can inadvertently submit something that breaks your web service, causing it to behave in some unexpected way.

## Types of Validation

### Server-side

Many new developers [fail to realize](http://security.stackexchange.com/questions/147216/hacker-used-picture-upload-to-get-php-code-into-my-site) that a malicious user could submit any type of request, with any content they like, to your server at any time. This is possible regardless of the forms and widgets that your web application presents to the client - it is a trivial matter to change their behavior using the [browser console](/troubleshooting/debugging#client-side-debugging), or bypass them completely using a command line tool such as [cURL](https://curl.haxx.se/docs/httpscripting.html).

For this reason, it is **imperative** to validate user input on the server side - *after* the request has left the control of the submitter.

### Client-side

You may wonder then, why client-side validation libraries like [the jQuery Validation plugin](https://jqueryvalidation.org/) exist at all. They serve no security purpose since they can be easily bypassed.

However, they *do* improve the experience of your everyday, non-malicious user. Regular users often enter invalid input, like a malformed email address or a credit card number with the wrong number of digits. Rather than submitting their request and then having the *server* tell them that they made a mistake, it is faster and more convenient if the client-side code can provide immediate feedback by validating their input *before* sending the request.

## Fortress

In summary, to build an application that is both secure **and** offers a smooth user experience, we need to perform both client- and server-side validation. Unfortunately, this creates a lot of duplicate logic.

UserFrosting Fortress solves this problem by providing a uniform interface for validating raw user input on both the client side (in Javascript) and on the server side (in PHP) using a single unified set of rules. It does this with a **request schema**, which defines what fields you're expecting the user to submit, and [rules](https://github.com/userfrosting/wdvss) for how to handle the contents of those fields. The request schema, which is a simple [YAML](http://symfony.com/doc/current/components/yaml/yaml_format.html) or JSON file, makes it easy to manipulate these rules in one place.

This process is summarized in the following flowchart:

![Flowchart for unified client- and server-side validation.](/images/flowchart-fortress.png?resize=800,600)

### Creating a Schema

Request schema are simple YAML or JSON files which live in your Sprinkle's `schema/` subdirectory. Simply create a new `.yaml` file:

**schema/requests/contact.yaml**

```yaml
# Request schema for the contact form

name:
  validators:
    required:
      label: Name
      message: Please tell us your name.
    length:
      label: Name
      min: 1
      max: 50
      message: "Name must be between {{min}} and {{max}} characters."
  transformations:
  - trim

email:
  validators:
    required:
      label: Email
      message: Please provide an email address.
    length:
      label: Email
      min: 1
      max: 150
      message: "Your email address must be between {{min}} and {{max}} characters."
    email:
      message: Please provide a valid email address.

phone:
  validators:
    telephone:
      label: Phone
      message: The phone number you provided is not valid.

message:
  validators:
    required:
      label: Message
      message: Surely you must have something to say!
```

Notice that the schema consists of a number of field names, which should correspond to the `name` attributes of the fields in your form. These map to objects containing `validators` and `transformations`. See [below](#schema-specifications) for complete specifications for the validation schema.

#### Loading Schema

To load a schema in your route callbacks and controller methods, simply pass the path to your schema to the `RequestSchema` constructor:

```php
// This line goes at the top of your file
use UserFrosting\Fortress\RequestSchema;

$schema = new RequestSchema('schema://requests/contact.yaml');
```

Notice that we've used the `schema://` stream wrapper, rather than having to hardcode an absolute file path. This tells UserFrosting to automatically scan the `schema/` subdirectories of each loaded Sprinkle for `contact.yaml`, and use the version found in the most recently loaded Sprinkle.

### Generating Client-side Rules

To automatically generate a set of client-side rules compatible with the [jQueryValidation](https://jqueryvalidation.org/) plugin, pass the `RequestSchema` object and your site's `Translator` object to the `JqueryValidationAdapter` class:

```php
// This line goes at the top of your file
use UserFrosting\Fortress\Adapter\JqueryValidationJsonAdapter;

// This assume $translator has been properly injected into the class or method.
$validator = new JqueryValidationJsonAdapter($this->translator);
```

The rules can then be retrieved via the `rules()` method, and the resulting array can be passed to a Twig template to be rendered as a Javascript variable:

```php
$rules = $validator->rules($schema);

return $this->view->render($response, 'pages/contact.html.twig', [
    'page' => [
        'validators' => [
            'contact' => $rules
        ]
    ]
]);
```

If your page includes the `pages/partials/page.js.twig` partial template, then the validation rules will become available via the Javascript variable `page.validators.contact`.

[notice=tip]For an example of how this all fits together, see the `UserFrosting\Theme\AdminLTE\Controller\RegisterPageAction` controller, and the template `pages/register.html.twig` from the AdminLTE sprinkle. At the bottom of the template you will see the include for `pages/partials/page.js.twig`.

If you visit the page `/account/register` and use "View Source", you can see how the validation rules have been injected into the page. See [exporting variables](/client-side-code/exporting-variables#page-specific-variables) for more details on exporting server-side variables to Javascript variables on a page.[/notice]

### Server-side Validation

To process data on the server, use the `RequestDataTransformer` and `ServerSideValidator` classes.

`RequestDataTransformer` will filter out any submitted fields that are not defined in the request schema (whitelisting), and perform any field [transformations](#transformations) defined in your schema:

```php
// These lines goes at the top of your file
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;

// Get submitted data (in POST)
$params = $request->getParsedBody();

// Load the request schema
$schema = new RequestSchema('schema://requests/contact.yaml');

// Whitelist and set parameter defaults
$transformer = new RequestDataTransformer();
$data = $transformer->transform($schema, $params);
```

`$data` will now contain your filtered, whitelisted data.

It's worth pointing out that we do not do any sort of "sanitization" on submitted data. Sanitization, an anti-pattern that should be [destroyed by fire](http://security.stackexchange.com/questions/42498/which-is-the-best-way-to-sanitize-user-input-in-php/42521#42521), creates a lot of problems when you end up wanting to use user-submitted data in multiple contexts.

**Data is not inherently dangerous; rather, it is the way you use it which can lead to security issues.** For this reason, mitigation against SQL injection and XSS are best handled using alternative methods. UserFrosting uses [prepared statements](http://php.net/manual/en/pdo.prepared-statements.php) (via Eloquent) to prevent SQL injection, and the Twig templating engine escapes user input in HTML to prevent XSS attacks.

Once you have filtered and whitelisted the input data, you can perform validation using `ServerSideValidator`:

```php
// These lines go at the top of your file
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;

$validator = new ServerSideValidator($this->translator);

// Add error messages and halt if validation failed
$errors = $validator->validate($schema, $data);
if (count($errors) !== 0) {
    $e = new ValidationException();
    $e->addErrors($errors);

    throw $e;
}
```

The `validate` method will return `false` if any fields fail any of their validation rules. Notice that we throw an exception to handle any error messages that we wish to display to the client, and stop the execution of the controller code.

[notice=info]Internally, UserFrosting uses the [Valitron](https://github.com/vlucas/valitron) validation package to perform server-side validation.[/notice]

### Input arrays
If your form uses [input arrays](https://stackoverflow.com/questions/4688880/html-element-array-name-something-or-name-something) such as `<input name='SomeInput[]'...`, you can reference the array itself in your validation schema as `SomeInput` and each element of the array as `SomeInput.*`

Transformations will only run if palced under the base array `SomeInput`, while most validators are run against each element `SomeInput.*` instead.
Please check [Valitron's usage directions](https://github.com/vlucas/valitron#usage) for more information on arrays and [multidimensional arrays](https://mattstauffer.com/blog/a-little-trick-for-grouping-fields-in-an-html-form/).

A useful schema for a phonebook might look like this:
```yaml
nameList:
  validators:
    required:
      message: Your input left out the names.
  transformations:
  - purge
  - trim

nameList.*:
  validators:
    length:
      min: 1
      max: 50
      message: "Names must be between {{min}} and {{max}} characters."

phoneList:
  validators:
    required:
      message: Your input left out the telephone numbers.
  transformations:
  - purge
  - trim

phoneList.*:
  validators:
    telephone:
      message: The phone number you provided is not a valid US phone number.
```

### Schema Specifications

#### Fields

A field consists of a unique **field name**, along with a set of attributes. The following attributes are defined for a field:

| Field name        | Optional | Description                                                                                                                                                                                                                    |
| ----------------- | :------: | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `transformations` |   Yes    | The `transformations` attribute specifies an ordered list of **data transformations** to be applied to the field.                                                                                                              |
| `validators`      |   Yes    | The `validators` attribute specifies an ordered list of **validators** to be applied to the field.                                                                                                                             |
| `default`         |   Yes    | The `default` attribute specifies a default value to be used if the field is not specified in the HTTP request. When a default value is applied, the data transformations and validators for the field shall be ignored. |

 **Example:**

 ```yaml
owls:
  validators:
    ...

  transformations:
    ...

  default: ...
```

#### Transformations

Data transformations should be applied before validation, in the specified order. The following transformations are currently supported:

| Transformations | Description                                                                                                                              |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| `purge`         | Remove all HTML entities (`'"<>&` and characters with ASCII value less than 32) from this field.                                         |
| `escape`        | Escape all HTML entities (`'"<>&` and characters with ASCII value less than 32).                                                         |
| `purify`        | Apply an HTML purification library, for example [HTMLPurifier](http://htmlpurifier.org/), to remove any potentially dangerous HTML code. |
| `trim`          | Remove any leading and trailing whitespace.                                                                                              |

 **Example:**
 ```yaml
comment:
  transformations:
  - purge
  - trim
```

#### Validators

A validator consists of a **validator name**, and a set of validator **attributes**. Each validator must have at least one attribute.

In addition to the rule-specific attributes described below, each validator may contain a **validation message** assigned to a `message` attribute. The validation message will be recorded during the call to `ServerSideValidator::validate` in the event that the field fails the validation rule. This can be a simple text message, or you may [reference a translatable string key](/i18n#the-placeholder) using the `&` prefix.

**Example:**
```yaml
talons:
  validators:
    required:
      label: "talons"
      message: "Talons is a required field."
    length:
      label: "talons"
      max: 120
      message: "Talons must be less than {{max}} characters."
```
Note there are two validators for `talons`. The `required` validator fails if the field has no value. The `length` validator sets a maximum of 120 characters.

The `message` key for each validator is simply the message that will be displayed if the validator parameters are not met. E.g. if a value of over 120 characters is provided, the user will see an alert message `Talons must be less than 120 characters.`

To integrate a translatable string key simply add your key using the `&` prefix. For example, your [translation file](/i18n#the-translation-files) might look like:

**locale/en_US/talons.php**
```php
return [
  'TALONS' => [
    'VALIDATE' => [
      'REQUIRED' => 'Talons is a required field.',
      'LENGTH'   => 'Talons must be less than {{max}} characters.'
    ]
  ]
];
```

And then in your schema file:
```yaml
talons:
  validators:
    required:
      label: "talons"
      message: "&TALONS.VALIDATE.REQUIRED"
    length:
      label: "talons"
      max: 120
      message: "&TALONS.VALIDATE.LENGTH"
```

Remember `&` is a special character in YAML, so using double-quotes is necessary.

The following validators are available:

| Name                     | Description                                                                                                                                                                  |
| ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `email`                  | Specifies that the value of this field must represent a valid email address.                                                                                                |
| `equals`                 | Specifies that the value of this field must be equivalent to a `value` attribute. (See example below)                                                                       |
| `integer`                | Specifies that the value of this field must represent an integer value.                                                                                                     |
| `length`                 | Specifies `min` and `max` attributes as bounds on the length (in characters) of this field's value. (See example below)                                                     |
| `matches`                | Specifies that the value of this field must be equivalent to the value of another field, named by the `field` attribute. (See example below)                                  |
| `member_of`              | Specifies that the value of this field must appear in the specified `values` array. (See example below)                                                                      |
| `no_leading_whitespace`  | Specifies that the value of this field must not have any leading whitespace characters.                                                                                     |
| `no_trailing_whitespace` | Specifies that the value of this field must not have any trailing whitespace characters.                                                                                    |
| `not_equals`             | Specifies that the value of this field must **not** be equivalent to the `value` attribute.                                                                                 |
| `not_matches`            | Specifies that the value of this field must **not** be equivalent the value of another field, named by the `field` attribute.                                                 |
| `not_member_of`          | Specifies that the value of this field must **not** appear in the specified `values` array.                                                                                  |
| `numeric`                | Specifies that the value of this field must represent a numeric (floating-point or integer) value.                                                                           |
| `range`                  | Specifies that the value of this field is greater than or equal to any `min` attribute, and less than or equal to any `max` attribute. (See example below)             |
| `regex`                  | Specifies that the value of this field must match a specified Javascript- and PCRE-compliant regular expression. (See example below)                                         |
| `required`               | Specifies that this field is a required field. If this field is not present in the HTTP request, validation will fail unless a default value has been specified for the field. |
| `telephone`              | Specifies that the value of this field must represent a valid US telephone number.                                                                                          |
| `uri`                    | Specifies that the value of this field must represent a valid Uniform Resource Identifier (URI).                                                                             |
| `username`               | Specifies that the value of this field must be a valid username (lowercase letters, numbers, `.`, `-`, and `_`).                                                            |

**Example - Equals:**
```yaml
owls:
  validators:
    equals:
      value: 5
      message: "Number of owls must be equal to {{value}}."
```

[notice=tip]By default, this is case-insensitive. It can be made case-sensitive by setting `caseSensitive` to `true`.[/notice]

**Example - Length:**
```yaml
screech:
  validators:
    length:
      min: 1
      max: 10
      message: "Your screech must be between {{min}} and {{max}} characters long."
```

**Example - Matches:**
```yaml
passwordc:
  validators:
    matches:
      field: password
      message: "The value of this field does not match the value of the 'password' field."
```

**Example - member_of:**
```yaml
genus:
  validators:
    member_of:
      values:
      - Megascops
      - Bubo
      - Glaucidium
      - Tyto
      - Athene
      message: Sorry, that is not one of the permitted genuses.
```

**Example - Range:**
 ```yaml
owls:
  validators:
    range:
      min: 5
      max: 10
      message: "Please provide {{min}} - {{max}} owls."
```
<!-- This next line is from the old [Web Data Validation Standard Schema (WDVSS)](https://github.com/userfrosting/wdvss) document, but I don't see these tags in UF or Valitron code.
[notice=tip]You can use `min_exclusive` instead of `min`, and `max_exclusive` instead of `max` to create open intervals.[/notice] -->

**Example - Regex:**
 ```yaml
screech:
  validators:
    regex:
      regex: ^who(o*)$
      message: You did not provide a valid screech.
```

[notice=warning]Regular expressions should _not_ be wrapped in quotes in YAML. Also the jQuery Validation plugin wraps regular expressions on the client side with `^...$`. Please see [this issue](https://github.com/jquery-validation/jquery-validation/issues/1967).[/notice]

### Limit rules to server or client only

Sometimes, you want a validation rule to be only applied server-side but not in Javascript on the client side, or vice versa. For example, there may be forms that contain hidden data that needs to be validated on the server-side, but is not directly manipulated by the user in the browser. Thus, these fields would not need client-side validation rules.

Alternatively, there might be fields that appear in the form that should be validated for the sake of user experience, but are not actually used by (or even sent to) the server.

To accomplish this, each validation rule can accept a `domain` property. Setting to "server" will have it only applied server-side. Setting to "client" will have it only appear in the client-side rules. If not specified, rules will be applied both server- and client-side by default. You can also set this explicitly with the value "both".
