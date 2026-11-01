---
title: Pages & Layout
description: A web page is a synthesis of HTML, CSS, Javascript, and other types of media. UserFrosting uses the powerful Twig templating engine to render web pages.
---

#### Chapter 8

# Pages & Layout

Building web pages by mixing PHP logic and HTML markup in the same file creates unreadable, unmaintainable code. You end up with security risks (like XSS vulnerabilities), difficulty in collaborating with designers, and code that's nearly impossible to test.

UserFrosting uses **Twig**, a powerful templating engine that cleanly separates your presentation (HTML) from your application logic (PHP). Twig provides a simple, secure syntax for displaying data, building layouts, and composing reusable template components—all while automatically escaping output to prevent security issues.

This chapter teaches you how to create templates, use Twig's features effectively, organize templates in your sprinkles, leverage UserFrosting's custom Twig extensions, and load assets in your pages. You'll learn to build beautiful, maintainable page layouts that keep your code clean and secure.
