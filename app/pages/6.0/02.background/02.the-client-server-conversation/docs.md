---
title: The Client-Server Conversation
description: Many developers do not really understand the basics of how HTTP and web applications work. This discussion attempts to clarify some common misconceptions.
wip: true
---

One of the most common misconceptions is that web applications are coherent pieces of software that sit on a server somewhere, and that the client "runs" this application in their browser. This is actually an illusion, carefully crafted to provide a smooth experience for the end user.

In reality, web applications are *conversations* between two agents with very poor memory - the **server**, and the **client** - which may be a web browser, mobile app, or another application. In modern web applications both the client *and* the server are typically going to need to run some code throughout their conversation. What's more, in the case of a PHP application, the client and server don't even speak the same language! The server runs only PHP, while the client runs only Javascript. (Note that there *are* server-side Javascript stacks, but we do not use them.)

## Server-side versus client-side code

Beginning developers are often confused by this, and you see questions on Stack Overflow like "how do I get my PHP in my Javascript" or "how do I keep the user from downloading the PHP and seeing my API keys?"  These questions come from a misconception that users are actually *downloading* PHP files and then somehow running the scripts in their browser. This misconception is exacerbated by the way that web servers like Apache, by default, use the name and relative path of a PHP script as the URL required to run that script. Thus, it's easy to conclude that visiting **http://owlfancy.com/admin/login.php** is causing your browser to download **login.php** and run the script in that file. But this is not the case!

### Server-side code

What actually happens is that users, through their browsers (clients), make a **request** to the **web server** for a **resource**. This is usually done via a Uniform **Resource** Locator, better known as a URL. The web server generates a **response** to the request, and in the case of image, Javascript, CSS, and other static resources, it often does simply return the contents of the corresponding file. But when the client requests a resource that is mapped to a PHP script, the web server doesn't return the contents of the script. Instead, it *executes* the script on the server and returns the *output* of that script.

In the case of a web page, the output is usually an HTML document. However, a server-side script could produce JSON, XML, Javascript, CSS, or even dynamically generated images as its response. What's important is that the actual code of the script is never sent to the client - if it were, this could open all sorts of security risks!

### Client-side code

There are cases when the server *does* need to send code back to the client. For example, we might want to allow the client to automatically check if the information filled into a form is syntactically valid, without having to submit another request to the server. For this to work, we need a language that is universally understood by all types of browsers and clients. Luckily for us, such a language exists - it's called Javascript.

When you visit any modern web page, you first get the page itself (usually an HTML document), but then it contains a bunch of references to images, Javascript files, CSS files, and all sorts of other resources that are meant to enhance your experience of the page. Your browser is smart enough to see those references, and automatically request those resources as well. Then when it's finished grabbing a resource, it takes some action - displaying an image on the page, running some Javascript code, or modifying the styles of elements on the page.

Thus, to answer the questions from earlier:

> How do I keep the user from downloading the PHP and seeing my API keys?

You don't need to do anything. Your web server is configured to do this automatically. As long as you haven't changed the default behavior of your server in some bizarre way, the actual PHP code will *never* be sent back to the client. Only the *output* of the code is sent in the response.

> How do I get PHP in my Javascript?

Again, clients can't run PHP in their browsers. If you want to pass along the values of some PHP variables to the Javascript you send back to the client, you need to explicitly *generate* Javascript variables using PHP. For example:

```html
<?php

    // This is a PHP variable - (note the leading $)
    $baseUrl = "https://owlfancy.com";

?>

<button id="updateButton" type="button">Update My Owl</button>

<script>
    // This is a Javascript variable - (note no leading $)
    let baseUrl = <?php echo $baseUrl; ?> ;

    // Our Javascript code can now reference the Javascript variable baseUrl
    $("#updateButton").on("click", function () {
        $("#myOwlLink").prop("attr", baseUrl + "/great-horned-owl");
    });
</script>
```

UserFrosting has a cleaner way of doing this using the Twig templating engine, but the principle is still the same.

For more complex PHP code that needs to be run in the middle of a block of Javascript code (for example, querying the database, which can *only* be done server-side), we'll need a way to let Javascript code ask the server to run some code on its behalf. Remember, the only way we can run code on the server is by making requests and then waiting for a response!

Fortunately, modern browsers support something called AJAX, which allows Javascript code to automatically make requests to the server. Thus, you might see something like:

```php
<?php
    $baseUrl = "https://owlfancy.com";
    $owlId = getUserOwlId();
?>

<button id="seeVoleHuntResults" type="button">See Vole Hunt Results</button>

<script>
    let baseUrl = "<?php echo $baseUrl; ?>";
    let owlId = "<?php echo $owlId; ?>";

    $("#seeVoleHuntResults").on("click", function () {
        // This is a jQuery AJAX function that generates another request to the server!
        $.getJSON(baseUrl + "/vole-hunt/today",
        {
            "owl_id": owlId
        }).done(function(data) {
            // This is what the browser does when the request is complete successfuly.
            alert(data['voles_caught']);
        });
    });
</script>
```

### The Big Picture

Let's take a step back and talk about what's really going on here.

*The client's browser...*

*requests a dynamically generated resource from the server...*

*which contains some HTML and Javascript...*

*that Javascript contains instructions for the user's browser...*

*one of which is to automatically ask the server for some JSON data whenever the user presses a certain button...*

*the browser waits until the user actually presses that button to make the request...*

*and then the browser displays the result that it got from the server's response.*

This convoluted process is what ends up confusing a lot of new web developers, even those with significant programming experience in other domains.

## Example Web Application Conversation

It might be easier to understand this whole process if we provide an example of a web application as a "conversation" between your browser and a webserver. Suppose you type (or click a link from Google for) **http://www.owlfancy.com/health**. Your browser starts by reaching out to the server **owlfancy.com**, introducing itself, and making a request:

**Your browser:** "Hi, my name is 74.125.70.102. I'm a Chrome browser, version 53.0.2785.116, running on MacOS, and blah blah blah (insert a bunch of other stuff about me). Can I please have whatever's at *http://www.owlfancy.com/health*?"

**owlfancy.com:** "Sure. Looks like for that resource, I'm supposed to run this bit of code over here. Let's see what happens when I do that...Ok, it's done!  Looks like it returned some HTML. Here you go. The status code is 200. Let me know if you need anything else. Bye!"

**Your browser:** "Hmm, according to this, I'm supposed to ask you for *jquery.js*, *bootstrap.js*, *pellet.js*, *bootstrap.css*, and something called */images/preening.jpg*. Can I have those as well please?  I'm also supposed to get *https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic*, but that's not your problem. I can ask *fonts.googleapis.com* myself.

**owlfancy.com: (for each requested item):** "Sure, here you go. Let me know if you need anything else. Bye!"

**Your browser:** "Ok, now I'll run all this CSS and Javascript code."  *Runs jquery.js, bootstrap.js, and pellet.js*

**Your browser:** "Hmm, looks like I'm supposed to give these boxes over here a border and shadow, change the font for this to Lora, and set it up so that when someone clicks "Forage", a picture of an owl swoops across the screen. Oh, it also wants me to tell Google Analytics and Facebook what I just did. Sure, after all my owner didn't tell me *not* to do that...I'll just send that information right now.

**Your browser:** "La la, waiting for my user to do something..."

***User clicks "Forage"***

**Your browser:** "Hey, someone just clicked 'Forage' - I'm supposed to do something now! Let's see. First I need to get a picture of an owl...excuse me, owlfancy.com?  Can I have *http://www.owlfancy.com/images/swoop.jpg*?"

**owlfancy.com:** "Sure, just a second. Here you go! (Hands your browser an image file) The status code is 200. Let me know if you need anything else. Bye!"

**Your browser:** "Thanks! I'll just put this picture right here and then make it swoop across the screen. Wow!"

**Your browser:** "La la, waiting for my user to do something else..."

***User clicks "Account"***

**Your browser:** "Oh hi, owlfancy.com?  Can I have whatever's at *https://www.owlfancy.com/account*?"

**owlfancy.com:** "Uhh, who the heck are you? Go ask for *https://www.owlfancy.com/login* instead. The status code is 302. Let me know if you need anything else. Bye!"

**Your browser:** "Ok, may I please have *https://www.owlfancy.com/login* then?"

**owlfancy.com:** "Sure, have some HTML and some more Javascript. The status code is 200. Let me know if you need anything else. Bye!"

**Your browser:** "I'll just show this to the user. Hmm, looks like a form of some kind. When they click 'Submit', I'm supposed to take whatever they put in this form and POST it to *https://www.owlfancy.com/login*. Good thing they're using HTTPS, or someone else on my network might see my user's password!"

***User types in 'VoleALaMode' for their username, and 'hunter2' for their password.***

***User presses 'submit'.***

**Your browser:** "Excuse me, owlfancy.com? I have something addressed for *https://www.owlfancy.com/login*. It says 'username: VoleALaMode, password: hunter2'.

**owlfancy.com:** "Sure thing, I'll see if I can find that user... (checks the database) Sorry, I didn't find anything for that combination of username and password! Here, have an error message: 'Your username or password is invalid.' The status code is 400. Let me know if you need anything else. Bye!"

**Your browser:** "Well that didn't go well. I guess I'd better break the news to my user. Hmm, according to the Javascript on this page, I'm supposed to show the user this error message in this box over here. I'll just go ahead and do that."

**Your browser:** "La la, waiting for my user to do something else..."

***User changes the username to 'VolesALaMode' and presses submit again***

**Your browser:** "Excuse me, owlfancy.com? I have something addressed for *https://www.owlfancy.com/login*. It says 'username: VolesALaMode, password: hunter2'.

**owlfancy.com:** "Sure thing, I'll see if I can find that user... (checks the database) Found her! Here, have a success message: 'Welcome back, VolesALaMode!' Also, take this special code: 'nabddsXGa4FK0JHCipeEnAVXy8'. Just show this to me with any other requests you make, and I'll know who you are. The status code is 302. You should probably ask me for *https://www.owlfancy.com/account* next. Let me know if you need anything else. Bye!"

**Your browser:** "Special code, eh? I'll just add this to my cookies for this site and send it back with any more requests I end up making. I'm sure that's what she wants me to do! Ok, now I'm supposed to ask for *https://www.owlfancy.com/account*..."

**Your browser:** "Excuse me, owlfancy.com?  Can I have *https://www.owlfancy.com/account*?  I also have this special code. It's 'nabddsXGa4FK0JHCipeEnAVXy8'."

**owlfancy.com:** "Sure thing. Let me see if I recognize that special code...hey, I know you, you're VolesALaMode! (erm, at least I hope it's you and not someone who stole your code!) Let me go look up a few things for you... Hmm, I'm supposed to get a list of your next HootMeets from the database, whatever a HootMeet is...ok, found it. I'll just lay them out in this nice HTML template and send it back. Here, have this HTML! The status code is 200. Let me know if you need anything else. Bye!"

**Your browser:** "Thanks! I'll just show my user this..."

***Browser now shows https://www.owlfancy.com/account***

**Your browser:** "There's some Javascript here too, so I'll just run that. Hmm, it's telling me that I should ask for *https://www.owlfancy.com/account/notifications*. Hi owlfancy.com, can I have that please? I also have this special code. It's 'nabddsXGa4FK0JHCipeEnAVXy8'."

**owlfancy.com:** "Sure thing. Hey, I know you, you're VolesALaMode! Here you go, have some JSON. The status code is 200. Let me know if you need anything else. Bye!"

**Your browser:** "Ok, what am I supposed to do with this? I'll just look back to the Javascript for this page. Let's see, it wants me to display these notifications with a green background and a light shadow over here, just below the navigation bar. Can do!"
