---
title: Debugging
metadata:
    description: When your application "doesn't work", it's not always obvious where the problem lies.  Modern web browsers come with a built-in tool for identifying problems in client-side code, as well as problems in the communication between your browser and the server.
taxonomy:
    category: docs
---

As we mentioned in [Chapter 2](/background/the-client-server-conversation), a web application is not a single piece of software.  It consists of the server running PHP code, the client (browser) running Javascript and rendering the web page, and the conversation between the two parties.  Things can go wrong in any of these three places.

## Client-side Debugging

When your application "doesn't work", it's not always obvious in which of these three places the problem lies.  In general, it's best to start debugging problems on the client side unless you already have a pretty good idea that the problem lies in your server-side code.  Fortunately, modern web browsers come with a built-in tool for identifying problems in client-side code, as well as problems in the communication between your browser and the server.  This tool is called the **browser console.**

The browser console can show you error and debug output from the Javascript interpreter, as well as the specific requests that your browser makes and the responses that it gets from the server.  It also lets you explore the DOM (the Document Object Model, which is basically the HTML that is rendered in the browser at any given moment), and the specific sequence of CSS transformations that are being applied to each HTML element.

### Firefox

To open the console in Firefox, use the following shortcuts:

 - Windows: <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>K</kbd>
 - MacOS: <kbd>Cmd</kbd> + <kbd>Opt</kbd> + <kbd>K</kbd>

![Firefox developer tools](/images/firefox-console-1.png)

The tool panel will open (usually docked at the bottom) and you'll notice some tabs at the top of this panel: **Inspector**, **Console**, **Debugger**, **Style Editor**, **Performance**, **Network**, and **Settings**.  By default, the Console tab should be selected (if not, click it).

![Firefox developer tools - Console tab](/images/firefox-console-2.png)

Underneath that, you'll probably see a long list of URLs.  These are the requests that your web browser has made so far.  To the left of each URL is the request method (GET, POST, PUT, DELETE, etc).  To the right is the server response summary.  For example, the server responded **HTTP/1.1 200 OK** for the request to **http://localhost/userfrosting/public/alerts**.  The status code **200** is used to mean that the request completed "successfully".  "Success" is something that your server defines - it's just a way for the server to tell your browser that everything went as expected and that it doesn't need to do anything else.

If you click the response summary, Firefox will open the **Network** tab and show you more details about the request:

![Firefox developer tools - Network tab](/images/firefox-console-3.png)

To the right, you'll see tabs for **Headers**, **Cookies**, **Params**, **Response**, and **Timings**.  If you scroll down in the Headers tab, you'll see a list of the request and response headers.  The request headers contain metadata sent with a particular request, and contain information about your browser, the contents of any cookies for the site, and other information.  The response headers contain metadata returned by the server for that request.  This can include information like cookies that the site wants the client to store or update, as well as things like the type of content (HTML, image, CSS, JSON, etc) being returned.

The **Cookies** tab just displays the contents of any cookie headers in a more easily read format.

#### Params tab

![Firefox developer tools - Params tab](/images/firefox-console-4.png)

**Params** shows any data sent in the *body* of the request.  **This is extremely useful for debugging client-side code.**  Often times, a web application appears to "not work" because the server isn't actually being sent the data it expected.  The **Params** tab is a good way to check the actual data that was sent with a particular request.

#### Response tab

![Firefox developer tools - Response tab](/images/firefox-console-5.png)

The **Response** tab shows the data sent in the *body* of the response.  For requests to a URL representing a web page (like the URL in your browser's navigation bar), the response body simply contains the actual HTML returned from the server, that your browser initially renders.  For other requests, it could contain images, Javascript, or structured data in some other format.  For example in the request shown above, the response contains structured JSON data representing a list of users currently registered with the application.

Again, this is useful in debugging because if you were expecting the client-side of the application to do something after making a particular request to the server, you can confirm that the response contains the data you are expecting.

### Other browsers

Other browsers have their own developer tools, which are similar to those found in Firefox.

#### Chrome

To open Chrome's DevTools feature, press:

 - Windows: <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>J</kbd>
 - MacOS: <kbd>Cmd</kbd> + <kbd>Opt</kbd> + <kbd>J</kbd>
 
#### Safari

Check the "Show Develop menu in menu bar" setting in "Settings" -> "Advanced" ([screenshot](https://developer.apple.com/library/mac/documentation/AppleApplications/Conceptual/Safari_Developer_Guide/GettingStarted/GettingStarted.html)).

Then, to open Safari's Web Inspector:

<kbd>Cmd</kbd> + <kbd>Opt</kbd> + <kbd>C</kbd>

#### Internet Explorer (and Edge)

To open Internet Explorer's Developer Tools, simply press <kbd>F12</kbd>.

*Credit to [this answer](http://webmasters.stackexchange.com/a/77337/50865) on Stack Overflow*.

Each browser's implementation of the features that we described for Firefox earlier is slightly different, but the information should all be there.

## Using Debug Print Statements
 
The most basic debugging tool, and probably the first thing you learned when you started programming, is the print statement.  Modern browsers provide their own version of the print statement, `console.log()`.  This can be used to output string literals as well as the values of variables (including structured data like arrays and objects) to the browser console.

For example, we can use it to inspect the contents of a JSON object returned by an AJAX call:

```
(function( $ ) {
    $.fn.flashAlerts = function() {
        var field = $(this);
        var url = site['uri']['public'] + "/alerts";
        return $.getJSON( url, {})
        .then(function( data ) {        // Pass the deferral back
            // Debugging statement
            console.log(data);
        
        ...
        });
    }
}
```

Now, when we do something that triggers this request, the `console.log` statement will print the contents of the variable `data` to the browser console:

![Firefox developer tools - Debug print statement](/images/firefox-console-6.png)

As you can see in the last line of the console output, `data` is an array containing Javascript objects.  If we click on "Object", a panel opens to the right that displays the contents of that object.

## Debugging AJAX Requests

For traditional `GET` and `POST` requests, such as navigating to a page or submitting a form using your browser's default submission handling, the default behavior of most browsers is to directly display the response it receives in the main viewport.  This is done automatically, so we don't even really think of it as a request-response transaction.

However with the rise of "Web 2.0" and more complex web applications, we've seen the widespread adoption of AJAX for submitting requests and working with response data, all without refreshing the page.  Often times, the user doesn't even know that a request has been issued!

In these cases, any error messages we receive from PHP via the server's response will get held up inside the XHR object that Javascript uses to process the request.  We'll never see these error messages unless we either:

1. Explicitly tell our AJAX handler to dump them into the DOM, or;
2. Manually inspect the contents of the response using the browser console tools.

This is hardly a convenient and predictable way to get at debugging and error messages!

To solve this problem, UserFrosting's [client-side components](/client-side-code/components) can automatically replace the current window's contents with the contents of the response body when an error (4xx or 5xx) code is returned during an AJAX request.

To enable this behavior, set `site.debug.ajax` to `true` in your Sprinkle's [configuration file](/configuration/config-files).

## Server-side Debugging

So far, we've seen some basic techniques for debugging problems in client-side code (Javascript), and problems in the way that data is being passed back in forth between the client and the server.  But what happens when the problem is in the server-side code, i.e., in the PHP?

### Error logs

To begin with, PHP natively supports error logging by setting the `log_errors` directive to `on`, and specifying a path to an error log file (what we will call the **php error log**) with the `error_log` directive.  Enabling `log_errors` and disabling `display_errors` will cause PHP to dump error messages to the log instead of the response.

`errors.log` is where most run-time error messages generated by uncaught exceptions are sent.  [Slim](http://www.slimframework.com/), the microframework upon which UserFrosting is built, [converts all PHP errors into exceptions](http://docs.slimframework.com/errors/overview/).  Thus, the majority of application error messages will be found in this log.  You can read more about how the error-handling system as a whole works in [Chapter 10](/error-handling).

### Mail errors

`mail.log` is a special logger for mail-related activity.  The underlying [phpMailer](https://github.com/PHPMailer/PHPMailer) instance that we use reports its SMTP activity to this log.  The level of detail can be specified with the `mail.smtp_debug` configuration value, using the values specified in the [PHPMailer documentation](https://github.com/PHPMailer/PHPMailer/blob/239d0ef38c1eea3e9f40bb949a9683aee9ca5c28/class.phpmailer.php#L318-L325):

- `0` No output
- `1` Commands
- `2` Data and commands
- `3` As 2 plus connection status
- `4` Low-level data output

### Debug statements

We can also manually send debugging messages to a log file.  This is useful when you want to inspect the value of a server-side variable at a particular point in your code ("dumping the variable") or determine if a particular method or function is being called.

To manually invoke the error logger, we can use the [`error_log`](http://php.net/manual/en/function.error-log.php) function.  This will let us write to the php error log:

```
// Print a simple string to the log
error_log("Fetching owls from database...");

// Print an array or object to the log
error_log(print_r($owls, true));
```

We can use this as a simple alternative to using `echo` to print debugging information directly to the response.

### Debug facade

It'd be nice if we could separate our reports of actual, unexpected errors from routine debugging statements, by sending them to separate log files.  UserFrosting supports this as well, and creates the following log files in the `app/logs/` directory:

- `debug.log`
- `errors.log`
- `mail.log`

`debug.log` is meant for dumping routine debugging statements.  To log something to the debugging log, simply use the `Debug` facade:

```
use UserFrosting\Sprinkle\Core\Facades\Debug;

...

Debug::debug("Fetching owls from database...");

Debug::debug("Owls found:", $owls);
```

`Debug` is a facade for a [Monolog](https://github.com/Seldaek/monolog) logger instance, whose `debug` method takes a string as the first parameter and an optional array as a second parameter, and writes them to a log file.  Monolog also supports more advanced logging capabilities - check their documentation for more details.
