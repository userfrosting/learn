---
title: Groups
description: Unlike roles, which assign specific sets of permissions to users, user groups allow you to horizontally partition your users.
wip: true
---

Every user can belong to **exactly one group**. Groups are used to horizontally partition your users - for example, if you have offices in Baltimore, London, and Munich, you might want to group your users according to which office they are in. Group membership can be used to broadly determine styling, layout, or access control for all users in the group.

Like roles, groups have a **name**, **slug**, and **description**. The slug is used as a unique, semantic identifier for the group and will show up in group-related URLs.

To set the default group for newly registered users, use the `site.registration.user_defaults.group` setting. This setting should be set to the slug for the default group.

#### Conditioning permissions on group membership

Groups are not directly associated with roles or permissions. A user in Baltimore and a user in London could have the exact same roles, but be in different groups. However, group membership can still influence a user's effective permissions _indirectly_ through a permission's [access conditions](users/access-control#Accessconditions).

For example, consider the default permission `view_group_field` with condition `equals_num(self.group_id,group.id) && in(property,['name','icon','slug','description','users'])`. A Baltimore user and a London user might both have this same permission, for example through the "Group Administrator" role.  But since the condition requires that the user's `group_id` match the target `group.id`, the Baltimore user will only see group fields for the Baltimore group, and the London user will only see group fields for the London group.
