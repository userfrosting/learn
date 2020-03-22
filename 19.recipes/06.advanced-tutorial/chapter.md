---
title: Advanced tutorial
metadata:
    description: A complete step-by-step guide to creating a complex page for UserFrosting. We'll set up a new "Pastry" database table and data model, and implement a page that displays a sortable, searchable table of these entities.
taxonomy:
    category: docs
---

# Advanced tutorial

This tutorial will guide you to create a fully featured page including custom data model, permission, template and localization components. This is the most complete guide (yet) to creating a custom page for UserFrosting and summarizes everything covered by this documentation so far. This guide is intended for advanced users. If you come across any terminology that you do not fully understand, please use the search bar to search the documentation for an explanation.

[notice]This tutorial assumes that the reader has already [set up their own sprinkle](/sprinkles/first-site) and is familiar with the base components, including [Twig](/templating-with-twig), [routing](/routes-and-controllers/front-controller), [Controller classes](/routes-and-controllers/controller-classes), and [Eloquent data models](/database/overview).[/notice]

For this exercise, we'll create a simple page which will display a list of "pastries" from a new database table. This page will be accessible at the `/pastries` route. We'll add an entry in the sidebar menu and set up basic permissions to control access to this page. All of this will be stored in its own Sprinkle, fully decoupled from the core UserFrosting codebase. Please note, we assume that you already have a clean instance of UserFrosting installed and running. Shall we begin?

[notice]This recipe was sponsored by [adm.ninja](https://adm.ninja). [Get in touch with the UserFrosting team](https://chat.userfrosting.com) if you want to sponsor a custom recipe for your organization![/notice]
