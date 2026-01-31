---
title: Changing user permissions
description: An example of how to give access to UserFrosting's default user and group pages to any registered users. Users are granted additional permissions through their roles.
wip: true
---

This recipe will guide you on how to give access to the users and groups pages to any registered users. To see the results, you'll need to use two user accounts: The root account and a non-root account. The root account will be used to change the roles from the UI and the non-root user to test those changes.

This example only covers the built-in permissions and roles. For a more in-depth discussion of adding custom permissions to your application, and managing additional roles, see the [Access Control](users/access-control) chapter.

> [!NOTE]
> This recipe was sponsored by [adm.ninja](https://adm.ninja). [Get in touch with the UserFrosting team](https://chat.userfrosting.com) if you want to sponsor a custom recipe for your organization

## Changing the permissions for the `User` role

The first steps are to edit the default permissions of the **User** role, which was automatically created when you installed UserFrosting. The goal here is to give read access to the built-in _Group Management page_ and _User Management page_ for users who have the **User** role. With the root account, go to the **Roles** page and click on **Manage Permissions** from the **Actions** dropdown of the **User** role.

![Default permission](/images/user-group-pages/default-permissions.png)

Add the following permission to the role:

- View group (View the group page of any group.)
- View group (View certain properties of any group.)
- Group management page
- View user
- User management page

![Modified permissions](/images/user-group-pages/new-permissions.png)

At this point, any user (assuming they have the _User_ role) should be able to see the `Groups` and `Users` links in the sidebar, as well as the list and details pages for users and groups.

![Public group page](/images/user-group-pages/result-groups.png)

![Public user page](/images/user-group-pages/result-users.png)

> [!NOTE]
> As of version 4.1.12, the **Action** dropdown in the user and group management tables still shows links to administrative functions, even if the current user doesn't actually have the necessary permissions. Clicking on any link will throw a `ForbiddenAccess` exception. This is a known limitation and only constitutes a minor user experience issue. It is **not** a security issue, as access is still controlled in the relevant server-side endpoints.
