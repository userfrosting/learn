---
title: Throttling
metadata:
    description: Throttling, also known as rate-limiting, is a technique for slowing down attackers by limiting the frequency with which they can make certain types of requests.
taxonomy:
    category: docs
---

People tend to be bad at picking strong passwords. [Publicly available lists of passwords](https://github.com/danielmiessler/SecLists/tree/master/Passwords) recovered from hacked databases reveal that, despite efforts to educate, users still pick the same highly predictable passwords over and over. These lists make it easy for brute-force attackers to gain unauthorized access to a large number of your users' accounts.

Complicated password policies (other than password length) [tend to backfire spectacularly](http://security.stackexchange.com/questions/6095/xkcd-936-short-complex-password-or-long-dictionary-passphrase/6116#6116). A good alternative then, is to slow down brute-force attackers to the point where it would take an inordinate amount of time to crack all but the easiest passwords.

This strategy is known as **throttling**, and should be employed in any route that could allow an attacker to gain unauthorized access or otherwise affect other users' accounts, such as the login and password recovery routes. UserFrosting supports throttling based on either IP address or some other chosen pieces of information (e.g. username).

## Defining throttles

Throttles can be defined in your configuration file, under the `throttles` key. This key contains a list of named throttle **event types**, each of which contains the following properties:

- `method`: Specifies the rule by which this throttle should be applied. If set to `ip`, then attempts on this event type from the same IP address will be rate-limited. If set to `data`, then attempts will be rate-limited based on the contents of the submitted request data. This can be used, for example, to limit sign-in attempts based on the username or email address.
- `interval`: The window of time in which to consider past attempts on this event type, in deciding whether or not to allow access.
- `delays`: A mapping of event counts (*x*) to delays (*y*, seconds). Any event of this throttle's type that has occurred more than *x* times in the interval, must wait *y* seconds after the last occurrence before another attempt is permitted.

For example, consider the throttle rule:

```php
'sign_in_attempt' => [
    'method'   => 'ip',
    'interval' => 3600,
    'delays' => [
        2 => 5,
        3 => 10,
        4 => 20,
        5 => 40,
        6 => 80,
        7 => 600
    ]
],
```

This defines a *throttleable* event `sign_in_attempt`, which throttles based on IP address. Each time this event is attempted, UserFrosting checks the `throttles` database table for any events of the same type from the same IP address in the past 3600 seconds. If fewer than 2 are found, the request is allowed to proceed immediately. Otherwise, the user receives an error with a `429` status code, and is told that they need to wait the specified number of seconds (as defined in `delays`) before they can attempt their request again.

[notice=tip]To disable a throttle, or all throttles completely, simply set the specific event key or the `throttles` key (respectively) to `null`.[/notice]

## Using throttles in controllers

Once you have defined your throttles, you must incorporate them into the desired controller methods. The procedure is generally defined as:

1. Determine the point in your controller at which you want the throttle to trigger. For example, you might not want malformed requests (failing validation checks) to count towards the throttling limit, but you would want an incorrect password or email address to count.
2. Determine the point in your controller at which you want the event to be recorded (if at all). For example, you might want to make your throttled event transactional with the rest of your controller's database activities, so that errors that are "our fault" do not count towards the throttle limit. You might also want to consider if successful attempts should count towards the throttle limit for future attempts of the same type.

This process is generally implemented using the `getDelay` and `logEvent` methods on the `throttler` service. For example, the following is an example from the Account Sprinkle `ForgetPasswordAction` :

```php

// ...

// Inject Throttler into the class
public function __construct(
    // ... 
    protected Throttler $throttler,
    // ...
) {
}

// ...

$delay = $this->throttler->getDelay('password_reset_request', [
    'email' => $data['email'],
]);

// ...

if ($delay > 0) {
    $e = new ThrottlerDelayException();
    $e->setDelay($delay);

    throw $e;
}

// ...

// Begin transaction - DB will be rolled back if an exception occurs
$this->db->transaction(function () use ($data) {

    // Log throttleable event
    $this->throttler->logEvent('password_reset_request', [
        'email' => $data['email'],
    ]);
    
    // ...

});

// ... 
```

You'll notice that we first check the `password_reset_request` throttle (the client IP address is automatically retrieved by the `throttler` service) and return an error if the computed delay is greater than 0. We do this *before* the call to `logEvent` - which adds a record of this attempt to the database - so that requests which are rejected because of the throttle rule do not further exacerbate the timeout period.

[notice=warning]IP-based throttling will not protect you as well from distributed attacks. One popular alternative, throttling based on username, opens your application to denial-of-service attacks because an attacker can simply lock users out of their accounts for potentially long periods of time by repeatedly making failed attempts on each account. See the [OWASP guide](https://www.owasp.org/index.php/Blocking_Brute_Force_Attacks) for more information on mitigating brute-force attacks.[/notice]
