---
title: Adding Form Modals
metadata:
    description: Adding form modals to allow Pastries to be created and deleted.
taxonomy:
    category: docs
---

At this point you should be able to navigate to the `/pastries` page and see a table with the default data we have added. Seeds are great for initial setup but you will probably want the ability to dynamically add, edit, and delete rows from the table without relying on a seed. Let's add `create`, `edit`, and `delete` modal forms to our table.

### Initial Setup

In our `templates` directory, add the sub directories `forms` and `modals`.

```
pastries
├──templates
   ├── forms
   ├── modals
   ├── navigation
   └── pages
```
