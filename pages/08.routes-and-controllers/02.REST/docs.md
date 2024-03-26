---
title: RESTful Design
metadata:
    description: Representational State Transfer (REST) is a design paradigm for efficient, scalable communication between clients and the server.
taxonomy:
    category: docs
---

Before we talk about the application itself, let's talk about how the client gets to the application in the first place: by making a **request**. A request consists of a few main components:

1. The **url**, for example `http://owlfancy.com/barn-owls` or `http://api.owlfancy.com/owls`;
2. The **method**, such as `GET`, `POST`, `PUT`, `DELETE`, etc;
3. A number of **request headers**, for example `User-Agent` or `Referer`;
4. The request **message body** which could contain, for example, a set of values from a form submission.

When HTTP was first designed, it was meant to reflect the transactional nature of communication between the client and the server. The idea is that the client is always making a request for a specific **resource** on the server, which is an abstract concept that could represent a web page, or a user account, a collection of user accounts, or pretty much any "thing" that you could think of.

A url is simply a way of identifying a resource. The HTTP method then tells the server what the client wants to _do_ with that resource. You can think of this as the grammar of a natural language, with the method acting as the verb and the url as the object of a sentence. Together, a specific url and method are commonly referred to as an **endpoint**.

In the years since, there has been a tendency to build more abstractions on top of this very basic language. However, we have been seeing lately an effort to get back to the roots of HTTP as it was intended to be used - this is what people are commonly referring to when they talk about [REST](https://en.wikipedia.org/wiki/REST).

## REST and PHP

The urls and methods that the client uses to interact with the server should be determined based on the **semantic design** of your application, rather than technological limitations. Unfortunately, this hasn't always been easy to do with PHP.

### The Bad Way

If you're coming from a "traditional" PHP background, you might be used to thinking of web pages as `.php` files. You'd have a file that lives somewhere in your document root:

```
www/
└── myNewbieProject/
    └── owls/
        └── barn_owl.php
```

Then you would be able to access the page at `http://example.com/myNewbieProject/owls/barn_owl.php`. Most web servers are configured to automatically map the portion of the url after the scheme (`http://example.com/`) to an actual file in the document root directory, where each slash represents a subdirectory and the last portion corresponds to the name of a PHP script.

This system is easy for newbies to understand, but it has a lot of limitations. First, it requires you to have a separate file for each web page that you want to generate. In a real application, you may want to have hundreds of thousands of very similar web pages, and it doesn't make sense to require a separate file for each page. Also, it couples the structure of your **code** to the structure of your **urls**. To generate semantically useful urls, we'd have to have a messy and complicated maze of directories on our server.

Within each file, you'd also need control structures (if/else) to have it do different things depending on which HTTP method was used. All of this makes it very cumbersome to implement a RESTful design for your endpoints.

### The Better Way

UserFrosting, and most other modern frameworks and content management systems, use a [front controller](/routes-and-controllers/front-controller) to solve this problem. With a front controller, the web server is configured to pass all requests to a single script - `index.php`. From there, the request endpoint is interpreted, and a matching **route** is invoked. These routes do not need to be defined in PHP files that match the name of the url. Thus, we've **decoupled** the endpoints from the directory structure of our application.

Having done this, we are now free to choose any url and method for any request - whether it's a page, form submission, API request, or whatever. This allows us to design our endpoints according to the principles of REST. The next section explains how we should think when we're choosing the urls and methods that our application exposes to the client.
