---
title: Advanced tutorial
metadata:
    description: Complete step by step guide to create a complex page
taxonomy:
    category: docs
---

# Advanced tutorial

This tutorial will guide you to create a fully featured page including custom data model, permission, template and localisation. This is the most complete guide (yet) about creating a custom page for UserFrosting and summarize everything covered by this documentation so far. This guide is indented for advanced users. If there's a word you didn't understood at this point, go back read some more pages!

>NOTE: This tutorial assumes that the reader already setup his own sprinkle and is familiar with all the base components, including Twig, Routing, Eloquent and Controllers.

For this exercice, we'll create a simple page which will displays a list of pastries from a new database table. This page will be accessible at the `/pastries` route, have it's own database table, basic permissions and entry in the sidebar menu. And all of this will be store in it's own sprinkle. At this point, we assume you already have a clean instance of Userfrosting installed and running. Shall we begin?

>>> This recipe was spronsored by @neurone. Get in touch with the UserFrosting team if you want to sponsor own receipe !
