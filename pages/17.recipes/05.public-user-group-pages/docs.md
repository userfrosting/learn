---
title: Public users and groups pages
metadata:
    description: Steps to create a complex page and add it to the default sidebar menu.
taxonomy:
    category: docs
---

>>>> This receipe requires UserFrosting version 4.1.12 or newer.

This recipe will guide you on how to give access to the users and groups pages to any registered users. To see the results, you'll need to use two user account: The root account and a non-root account. The root account will be used to change the roles from the UI and the non-root user to test those changes.

>>> This recipe was spronsored by [adm.ninja](https://adm.ninja). Get in touch with the UserFrosting team if you want to sponsor own receipe !

## Changing the User role permission

First steps are to edit the default permission of the default **User** roles. The goal here is to give read access to the built-in _Group Management page_ and _User Management page_ for the users who have the **User** role. With the root account, go to the **Roles** page and click on ** ** from the **Actions** dropdown of the **User** role.

![Default permission](/images/user-group-pages/default-permissions.png)

Add the following permission to the role:

- View group (View the group page of any group.)
- View group (View certain properties of any group.)
- Group management page
- View user
- User management page

![New permission](/images/user-group-pages/new-permissions.png)

At this point, any user (with the _User_ role only) should be able to see the `Groups` and `Users` links in the sidebar and be able to see the list and details page for each one.

![Public group page](/images/user-group-pages/result-groups.png)

![Public user page](/images/user-group-pages/result-users.png)

>>>>> As of version 4.1.12, the **Action** dropdown will still shows the administrative functions. Clicking on any link will thrw a `ForbiddenAccess` exception. This will be patched in future version.