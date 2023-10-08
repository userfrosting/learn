---
title: The Mailer Service
metadata:
    description: UserFrosting's mailer service integrates smoothly with Twig to create dynamically generated messages.  Templated messages can be sent to large groups of recipients, customizing the content for each recipient.
taxonomy:
    category: docs
---

## Configuration

Once you have selected a mail service provider, you can configure your host, username, and password in your environment variables (or `.env` file):

| Varible         | Description                                        |
| --------------- | -------------------------------------------------- |
| `SMTP_HOST`     | Your mail host (e.g., `smtp.gmail.com`)            |
| `SMTP_USER`     | Your account username (e.g., `owlfancy@gmail.com`) |
| `SMTP_PASSWORD` | Your account password                              |

For more advanced configuration, you can override the `mail` configuration values in your site sprinkle configuration :

```php
'mail'    => [
    'mailer'          => 'smtp', // Set to one of 'smtp', 'mail', 'qmail', 'sendmail'
    'host'            => env('SMTP_HOST') ?: null,
    'port'            => 587,
    'auth'            => true,
    'secure'          => 'tls', // Enable TLS encryption. Set to `tls`, `ssl` or `false` (to disabled)
    'username'        => env('SMTP_USER') ?: null,
    'password'        => env('SMTP_PASSWORD') ?: null,
    'smtp_debug'      => 4,
    'message_options' => [
        'CharSet'   => 'UTF-8',
        'isHtml'    => true,
        'Timeout'   => 15,
    ],
],
```

The `smtp_debug` setting determines the verbosity of logs that are sent to your `mail.log`:

| Value       | Description                 |
| ----------- | --------------------------- |
| 0           | No output                   |
| 1           | Commands                    |
| 2           | Data and commands           |
| 3           | As 2 plus connection status |
| 4 (default) | Low-level data output       |

## Creating messages

To create a new email message, create a new template in your Sprinkle's `templates/mail/` directory:

**confirm-owl.html.twig**

```html
{% block subject %}
    Please confirm your owl {{new_owl.name}}
{% endblock %}

{% block body %}
<p>
    Dear {{user.first_name}},
</p>
<p>
    Your owl has been accepted.  Please use the link to verify your relationship with this owl:
    <a href="{{site.uri.public}}/account/owls/{{new_owl.id}}/verify?token={{new_owl.token}}">
        {{site.uri.public}}/account/owls/{{new_owl.id}}/verify?token={{new_owl.token}}
    </a>
</p>
<p>
    With regards,<br>
    The {{site.title}} Team
</p>
{% endblock %}
```

You'll notice that the message contains two separate blocks for the `subject` and `body` of your message.

To load your message in your controllers, create a new `TwigMailMessage` object:

```php
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;

// ...

/**
 * Inject dependencies.
 */
public function __construct(
    protected Twig $twig,
    protected Mailer $mailer,
) {
}    

// ...

$message = new TwigMailMessage($this->view, "mail/confirm-owl.html.twig");
```

### Senders, recipients, and customized content

To set the `From` field for your message, simply call the `from` method on your message, passing in an array containing `email`, `name`, and optionally, `reply_email` and `reply_name` values:

```php
$message->from([
    'email' => 'david@owlfancy.com',
    'name' => 'The Owl Fancy Team'
]);
```

If you don't set `reply_email` and `reply_name`, `email` and `name` will be used.

You can add one or more recipients by creating a new `EmailRecipient` object and passing it into the `addEmailRecipient` method:

```php
$message->addEmailRecipient(
    new EmailRecipient($user->email, $user->full_name)
);
```

You can call the `cc` and `bcc` methods on your new `EmailRecipient` object to add CC and BCC fields for the recipient.

```php
$recipient = new EmailRecipient($user->email, $user->full_name);
$recipient->cc('owllover@gmail.com', 'Owl Lover');
$recipient->bcc('hawkfancier@hotmail.com', 'Hawk Fancier');
$message->addEmailRecipient($recipient);
```

You can set recipient-specific Twig parameters by passing in an array to the last parameter of the `EmailRecipient` constructor:

```php
$message->addEmailRecipient(
    new EmailRecipient($user1->email, $user1->full_name, [
        'new_owl' => $user1->newOwl
    ])
);
$message->addEmailRecipient(
    new EmailRecipient($user2->email, $user2->full_name, [
        'new_owl' => $user2->newOwl
    ])
);
```

Alternatively, you can use the `addParams` method to pass in common Twig parameters for all recipients:

```php
$message->addParams([
    'time' => Carbon::now()->format('Y-m-d H:i:s')
]);
```

All methods for `TwigMailMessage` can be fluently chained together into a single statement.

### Sending your message

To actually send your message, pass your `TwigMailMessage` into the `sendDistinct` method of your `mailer` service:

```php
$this->mailer->sendDistinct($message);
```

`sendDistinct` will send a **separate** email to each recipient, passing in any custom data that was defined in the `EmailRecipient` constructor. If you are trying to send a single message to a list of recipients, just use `send` instead. Note that in the case of `send`, any recipient-specific Twig parameters will be ignored.

[notice=note]By default, `sendDistinct` and `send` will clear the list of recipients from your message object after successfully sending. To prevent this from happening (for example, if you want to send the message again), you can set the second parameter of either of these methods to `true`.[/notice]

### Error handling

If a problem arises while attempting to send a message, a `phpmailerException` will be thrown. By default, this exception is caught by the `Error/Handler/PhpMailerExceptionHandler` (defined in the `core` Sprinkle). Of course, you can [define your own exception handler](/advanced/error-handling) instead.
