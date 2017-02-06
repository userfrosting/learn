---
title: The Mailer Service
metadata:
    description: UserFrosting's mailer service integrates smoothly with Twig to create dynamically generated messages.  Templated messages can be sent to large groups of recipients, customizing the content for each recipient.
taxonomy:
    category: docs
---

## Configuration

Once you have selected a mail service provider, you can configure your host, username, and password in your environment variables (or `.env` file):

- `SMTP_HOST`: Your mail host (e.g., `smtp.gmail.com`)
- `SMTP_USER`: Your account username (e.g., `owlfancy@gmail.com`)
- `SMTP_PASSWORD`: Your account password

For more advanced configuration, override the `mail` configuration values:

```
'mail'  => [
    'mailer'     => 'smtp',     // Set to one of 'smtp', 'mail', 'qmail', 'sendmail'
    'host'       => getenv('SMTP_HOST'),
    'port'       => 587,
    'auth'       => true,
    'secure'     => 'tls',
    'username'   => getenv('SMTP_USER'),
    'password'   => getenv('SMTP_PASSWORD'),
    'smtp_debug' => 4,
    'message_options' => [
        'isHtml' => true,
        'Timeout' => 15
    ]
]
```

The `smtp_debug` setting determines the verbosity of logs that are sent to your `mail.log`:

- `0` - No output
- `1` - Commands
- `2` - Data and commands
- `3` - As 2 plus connection status
- `4` - Low-level data output

## Creating messages

To create a new email message, create a new template in your Sprinkle's `templates/mail/` directory:

**confirm-owl.html.twig**

```
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

```
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;

...

$message = new TwigMailMessage($this->ci->view, "mail/confirm-owl.html.twig");
```

## Senders, recipients, and customized content

To set the `From` field for your message, simply call the `from` method on your message, passing in an array containing `email`, `name`, and optionally, `reply_email` and `reply_name` values:

```
$message->from([
    'email' => 'david@owlfancy.com',
    'name' => 'The Owl Fancy Team'
]);
```

If you don't set `reply_email` and `reply_name`, `email` and `name` will be used.

You can add one or more recipients by creating a new `EmailRecipient` object and passing it into the `addEmailRecipient` method:

```
$message->addEmailRecipient(
    new EmailRecipient($user->email, $user->full_name)
);
```

You can call the `cc` and `bcc` methods on your new `EmailRecipient` object to add CC and BCC fields for the recipient.

```
$message->addEmailRecipient(
    (new EmailRecipient($user->email, $user->full_name))
        ->cc('owllover@gmail.com', 'Owl Lover')
        ->bcc('hawkfancier@hotmail.com', 'Hawk Fancier');
);
```

You can set recipient-specific Twig parameters by passing in an array to the last parameter of the `EmailRecipient` constructor:

```
$message->addEmailRecipient(
    new EmailRecipient($user1->email, $user1->full_name, [
        'new_owl' => $user1->newOwl
    ])
)->addEmailRecipient(
    new EmailRecipient($user2->email, $user2->full_name, [
        'new_owl' => $user2->newOwl
    ])
);
```

Alternatively, you can use the `addParams` method to pass in common Twig parameters for all recipients:

```
$message->addParams([
    'time' => Carbon::now()->format('Y-m-d H:i:s')
]);
```

All methods for `TwigMailMessage` can be fluently chained together into a single statement.

## Sending your message

To actually send your message, pass your `TwigMailMessage` into the `sendDistinct` method of your `mailer` service:

```
$this->ci->mailer->sendDistinct($message);
```

`sendDistinct` will send a **separate** email to each recipient, passing in any custom data that was defined in the `EmailRecipient` constructor.  If you are trying to send a single message to a list of recipients, just use `send` instead.  Note that in the case of `send`, any recipient-specific Twig parameters will be ignored.

>>>>> By default, `sendDistinct` and `send` will clear the list of recipients from your message object after successfully sending.  To prevent this from happening (for example, if you want to send the message again), you can set the second parameter of either of these methods to `true`.

## Error handling

If a problem arises while attempting to send a message, a `phpmailerException` will be thrown.  By default, this exception is caught by the `PhpMailerExceptionHandler`.  Of course, you can [define your own exception handler](/error-handling/overview) instead.
