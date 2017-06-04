---
title: Validation
metadata:
    description: Client- and server-side validation are unified into one convenient interface using UserFrosting's Fortress package and a common set of rules defined in a JSON schema file.
taxonomy:
    category: docs
---

The number one security rule in web development is: **never trust client input!**

Data from the outside world is the Achilles' heel of modern interactive web services and applications.  Code injection, cross-site scripting (XSS), CSRF, and many other types of malicious attacks are successful when a web application accepts user input that it shouldn't have, or fails to neutralize the damaging parts of the input.  Even non-malicious users can inadvertently submit something that breaks your web service, causing it to behave in some unexpected way.

## Types of Validation

### Server-side

Many new developers [fail to realize](http://security.stackexchange.com/questions/147216/hacker-used-picture-upload-to-get-php-code-into-my-site) that a malicious user could submit any type of request, with any content they like, to your server at any time.  This is possible regardless of the forms and widgets that your web application presents to the client - it is a trivial matter to change their behavior using the [browser console](/background/client-side), or bypass them completely using a command line tool such as [cURL](https://curl.haxx.se/docs/httpscripting.html).

For this reason, it is **imperative** to validate user input on the server side - *after* the request has left the control of the submitter.

### Client-side

You may wonder then, why client-side validation libraries like [the jQuery Validation plugin](https://jqueryvalidation.org/) exist at all.  They serve no security purpose since they can be easily bypassed.

However, they *do* improve the experience of your everyday, non-malicious user.  Regular users often enter invalid input, like a malformed email address or a credit card number with the wrong number of digits.  Rather than submitting their request and then having the *server* tell them that they made a mistake, it is faster and more convenient if the client-side code can provide immediate feedback by validating their input *before* sending the request.

## Fortress

In summary, to build an application that is both secure **and** offers a smooth user experience, we need to perform both client- and server-side validation.  Unfortunately, this creates a lot of duplicate logic.

Fortress solves this problem by providing a uniform interface for validating raw user input on both the client side (in Javascript) and on the server side (in PHP) using a single unified set of rules.  It does this with a **request schema**, which defines what fields you're expecting the user to submit, and [rules](https://github.com/userfrosting/wdvss) for how to handle the contents of those fields.  The request schema, which is simply a JSON document, makes it easy to manipulate these rules in one place.

This process is summarized in the following flowchart:

![Flowchart for unified client- and server-side validation.](/images/flowchart-fortress.png?resize=800,600)

### Creating a Schema

Request schema are simple JSON files which live in your Sprinkle's `schema/` subdirectory.  Simply create a new `.json` file:

**schema/contact.json**

```json
    "name" : {
        "validators" : {
            "required" : {
                "label" : "Name",
                "message" : "Please tell us your name."
            },
            "length" : {
                "label" : "Name",
                "min" : 1,
                "max" : 50,
                "message" : "Name must be between {{min}} and {{max}} characters."
            }
        },
        "transformations" : ["trim"]
    },
    "email" : {
        "validators" : {
            "required" : {
                "label" : "Email",
                "message" : "Please provide an email address."
            },
            "length" : {
                "label" : "Email",
                "min" : 1,
                "max" : 150,
                "message" : "Your email address must be between {{min}} and {{max}} characters."
            },
            "email" : {
                "message" : "Please provide a valid email address."
            }
        }
    },
    "phone" : {
        "validators" : {
            "telephone" : {
                "label" : "Phone",
                "message" : "The phone number you provided is not valid."
            }
        }
    },
    "message" : {
        "validators" : {
            "required" : {
                "label" : "Message",
                "message" : "Surely you must have something to say!"
            }
        }
    }
```

Notice that the schema consists of a number of field names, which should correspond to the `name` attributes of the fields in your form.  These map to objects containing `validators` and `transformations`.  See [below](#schema-specifications) for complete specifications for the validation schema.

#### Loading Schema

To load a schema in your route callbacks and controller methods, simply pass the path to your schema to the `RequestSchema` constructor:

```php
// This line goes at the top of your file
use UserFrosting\Fortress\RequestSchema;

$schema = new RequestSchema('schema://contact.json');
```

Notice that we've used the `schema://` stream wrapper, rather than having to hardcode an absolute file path.  This allows UserFrosting to automatically scan the `schema/` subdirectories of each loaded Sprinkle for `contact.json`, and using the version found in the most recently loaded Sprinkle.

### Generating Client-side Rules

To automatically generate a set of client-side rules compatible with the [jQueryValidation](https://jqueryvalidation.org/) plugin, pass the `RequestSchema` object and your site's `MessageTranslator` object to the `JqueryValidationAdapter` class:

```php
// This line goes at the top of your file
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;

$validator = new JqueryValidationAdapter($schema, $this->ci->translator);
```

The rules can then be retrieved via the `rules()` method, and the resulting array can be passed to a Twig template to be rendered as a Javascript variable:

```php
$rules = $validator->rules('json', false);

return $this->ci->view->render($response, 'pages/contact.html.twig', [
    'page' => [
        'validators' => [
            'contact' => $rules
        ]
    ]
]);
```

If your page includes the `components/page.js.twig` partial template, then the validation rules will become available via the Javascript variable `page.validators.contact`.

>>>>>> For an example of how this all fits together, see the controller method `AccountController::pageRegister`, and the template `pages/register.html.twig`.  At the bottom of the template you will see the include for `components/page.js.twig`.  If you visit the page `/account/register` and use "View Source", you can see how the validation rules have been injected into the page.  See [exporting variables](/client-side-code/exporting-variables#page-specific-variables) for more details on exporting server-side variables to Javascript variables on a page.

### Server-side Validation

To process data on the server, use the `RequestDataTransformer` and `ServerSideValidator` classes.

`RequestDataTransformer` will filter out any submitted fields that are not defined in the request schema (whitelisting), and perform any field [transformations](#transformations) as defined in your schema:

```php
// These lines goes at the top of your file
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;

// Get submitted data
$params = $request->getParsedBody();

// Load the request schema
$schema = new RequestSchema('schema://contact.json');

// Whitelist and set parameter defaults
$transformer = new RequestDataTransformer($schema);
$data = $transformer->transform($params);
```

`$data` will now contain your filtered, whitelisted data.

It's worth pointing out that we do not do any sort of "sanitization" on submitted data.  Sanitization, an anti-pattern that should be [destroyed by fire](http://security.stackexchange.com/questions/42498/which-is-the-best-way-to-sanitize-user-input-in-php/42521#42521), creates a lot of problems when you end up wanting to use user-submitted data in multiple contexts.

**Data is not inherently dangerous; rather, it is the way you use it which can lead to security issues.**  For this reason, mitigation against SQL injection and XSS are best handled using alternative methods.  UserFrosting uses [prepared statements](http://php.net/manual/en/pdo.prepared-statements.php) (via Eloquent) to prevent SQL injection, and the Twig templating engine escapes user input in HTML to prevent XSS attacks.

Once you have filtered and whitelisted the input data, you can perform validation using `ServerSideValidator`:

```php
// These lines goes at the top of your file
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;

/** @var UserFrosting\Sprinkle\Core\MessageStream $ms */
$ms = $this->ci->alerts;

$validator = new ServerSideValidator($schema, $this->ci->translator);

// Add error messages and halt if validation failed
if (!$validator->validate($data)) {
    $ms->addValidationErrors($validator);
    return $response->withStatus(400);
}
```

The `validate` method will return `false` if any fields fail any of their validation rules.  Notice that we use the `alerts` service to store any error messages that we wish to display to the client.

>>> Internally, UserFrosting uses the [Valitron](https://github.com/vlucas/valitron) validation package to perform server-side validation. 

### Schema Specifications

#### Fields

A field consists of a unique **field name**, along with a set of attributes.  The following attributes are defined for a field:

##### `transformations` (optional)

The `transformations` attribute specifies an ordered list of **data transformations** to be applied to the field.

##### `validators` (optional)

The `validators` attribute specifies an ordered list of **validators** to be applied to the field.

##### `default` (optional)

The `default` attribute specifies a default value to be used if the field has not been specified in the HTTP request.  When a default value is applied, the data transformations and validators for the field shall be ignored.

**Example:**

```json
"owls" : {
    "validators" : {
        ...
    },
    "transformations" : [
        ...
    ],
    "default" : ...
}
```

#### Transformations

Data transformations should be applied before validation, in the specified order.  The following transformations are currently supported:

##### `purge`

Remove all HTML entities (`'"<>&` and characters with ASCII value less than 32) from this field.

**Example:**

```json
"comment" : {
    "transformations" : [
        "purge"
    ]
}
```

##### `escape`

Escape all HTML entities (`'"<>&` and characters with ASCII value less than 32).

##### `purify`

Apply an HTML purification library, for example [HTMLPurifier](http://htmlpurifier.org/), to remove any potentially dangerous HTML code.

##### `trim`

Remove any leading and trailing whitespace.

#### Validators

A validator consists of a **validator name**, and a set of validator attributes.  In addition to the rule-specific attributes described below, each validator may contain a **validation message** assigned to a `message` attribute.

The validation message will be recorded during the call to `ServerSideValidator::validate` in the event that the field fails the validation rule.  This can be a simple text message, or you may [reference a translatable string key](/advanced/i18n#the-placeholder) using the `&` prefix.

The following validators are available:

##### `email`

Specifies that the value of the field must represent a valid email address.

##### `equals`

Specifies that the value of the field must be equivalent to `value`.

```json
"owls" : {
    "validators" : {
        "equals" : {
            "value" : 5,
            "message" : "Number of owls must be equal to {{value}}."
        }
    }
}
```

By default, this is case-insensitive.  It can be made case-sensitive by setting `caseSensitive` to `true`.

##### `integer`

Specifies that the value of the field must represent an integer value.

##### `length`

Specifies `min` and `max` bounds on the length, in characters, of the field's value.

```json
"screech" : {
    "validators" : {
        "length" : {
            "min" : 1,
            "max" : 10,
            "message" : "Your screech must be between {{min}} and {{max}} characters long."
        }
    }
}
```

##### `matches`

Specifies that the value of the field must be equivalent to the value of `field`.

```json
"passwordc" : {
    "validators" : {
        "matches" : {
            "field" : "password",
            "message" : "The value of this field does not match the value of the 'password' field."
        }
    }
}
```

##### `member_of`

Specifies that the value of the field must appear in the specified `values` array.

```json
"genus" : {
    "validators" : {
        "member_of" : {
            "values" : ["Megascops", "Bubo", "Glaucidium", "Tyto", "Athene"],
            "message" : "Sorry, that is not one of the permitted genuses."
        }
    }
}
```

##### `no_leading_whitespace`

Specifies that the value of the field must not have any leading whitespace characters.

##### `no_trailing_whitespace`

Specifies that the value of the field must not have any trailing whitespace characters.

##### `not_equals`

Specifies that the value of the field must **not** be equivalent to `value`.  By default, this is case-insensitive.  It can be made case-sensitive by setting `caseSensitive` to `true`.

##### `not_matches`

Specifies that the value of the field must **not** be equivalent to the value of `field`. 

##### `not_member_of`

Specifies that the value of the field must **not** appear in the specified `values` array.

##### `numeric`

Specifies that the value of the field must represent a numeric (floating-point or integer) value.

##### `range`

Specifies a numeric interval bound on the field's value.  The `range` validator supports the following attributes:

```json
"owls" : {
    "validators" : {
        "range" : {
            "min" : 5,
            "max" : 10,
            "message" : "Please provide {{min}} - {{max}} owls."
        }
    }
}
```

You can use `min_exclusive` instead of `min`, and `max_exclusive` instead of `max` to create open intervals.

##### `regex`

Specifies that the value of the field must match a specified Javascript- and PCRE-compliant regular expression.

```json
"screech" : {
    "validators" : {
        "regex" : {
            "regex" : "^who(o*)$",
            "message" : "You did not provide a valid screech."
        }
    }
}
```

>>>> The jQuery Validation plugin, for some unholy reason, wraps regular expressions on the client side with `^...$`.  Please see [this issue](https://github.com/jquery-validation/jquery-validation/issues/1967).

##### `required`

Specifies that the field is a required field.  If the field is not present in the HTTP request, validation will fail unless a default value has been specified for the field.

##### `telephone`

Specifies that the value of the field must represent a valid telephone number.

##### `uri`

Specifies that the value of the field must represent a valid Uniform Resource Identifier (URI).

##### `username`

Specifies that the value of the field must be a valid username (lowercase letters, numbers, `.`, `-`, and `_`).

#### Limit rules to server or client only

Sometimes, you only want a validation rule to be applied server-side but not in Javascript on the client side, or vice versa.  For example, there may be forms that contain hidden data that needs to be validated on the server-side, but is not directly manipulated by the user in the browser. Thus, these fields would not need client-side validation rules.

Alternatively, there might be fields that appear in the form that should be validated for the sake of user experience, but are not actually used by (or even sent to) the server.

To accomplish this, each validation rule can accept a `domain` property.  Setting to "server" will have it only applied server-side.  Setting to "client" will have it only appear in the client-side rules.  If not specified, rules will be applied both server- and client-side by default.  You can also set this explicitly with the value "both".
