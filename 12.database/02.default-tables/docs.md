---
title: Default Tables
metadata:
    description: UserFrosting's installer creates a number of tables by default.  Here, we explain the purpose of each table.
taxonomy:
    category: docs
---

When you install UserFrosting with the [Bakery CLI](/cli), a number of tables will automatically added to your database.  These tables are required for UserFrosting's built-in features, such as user accounts, request throttling, persistent sessions, and access control.

The [migrations](/database/migrations) for most tables can be found in the `src/Database/Migrations` directory of the Sprinkle that depends on it.  The exceptions are the system tables, which are located in `app/system/Database/Migrations`.

## System tables

### `migrations`

The `migrations` table is responsible for maintaining a history of the [migrations](/database/migrations) run by the installer.  In general, you shouldn't need to interact with this table in your own code.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | autoincrement `int` | The unique identifier for the record. |
| `sprinkle` | `string(255)` | The name of the Sprinkle to which this migration belongs. |
| `migration` | `string(255)` | The fully qualified namespace+class name of the migration. |
| `batch` | `int` |  A counter that groups migrations that were run together in a single instance of the `migrate` command. Each run of `migrate` increments this counter. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

## Core tables

The `core` Sprinkle depends on the following tables:

### `sessions`

This is an optional table, and is only used if you are using the database [session driver](/advanced/sessions).

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `user_id` | unsigned `int` | For compatibility with Laravel session drivers only. |
| `ip_address` | `string(45)` | For compatibility with Laravel session drivers only. |
| `user_agent` | `text` | For compatibility with Laravel session drivers only. |
| `payload` | `text` | The base-64 encoded contents of the session. |
| `last_activity` | `integer` | The time when the session was last written. |

### `throttles`

This table stores a history of requests to [throttled endpoints](/routes-and-controllers/client-input/throttle).

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `type` | `string(255)` | The throttled [event type](/routes-and-controllers/client-input/throttle#DefiningThrottles). |
| `ip` | `string(255)` | The IP address of the requester. |
| `request_data` | `text` | Additional request data to compare when using the `data` throttling method. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

## Account tables

The `account` Sprinkle depends on the following tables:

### `users`

This table contains records for each user.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the user. |
| `user_name` | `string(50)` | A unique text identifier for the user. Only `[a-zA-Z0-9_-]` characters are allowed. |
| `email` | `string(255)` | The email address of the user. Must be unique. |
| `first_name` | `string(20)` | The user's first name.  Optional. |
| `last_name` | `string(30)` | The user's last name.  Optional. |
| `locale` | `string(10)` | The [language and locale](/i18n) to use for this user. |
| `theme` | `string(100)` | The user theme (not yet fully implemented). |
| `group_id` | unsigned `int` | The id of the user's [group](/users/groups). |
| `flag_verified` | `bool` | Set to 1 if the user has verified their account via email, 0 otherwise. |
| `flag_enabled` | `bool` | Set to 1 if the user account is currently enabled, 0 otherwise.  Disabled accounts cannot be logged in to, but they retain all of their data and settings. |
| `last_activity_id` | unsigned `int` | The id of the last [activity](/users/activity-logging) performed by this user. |
| `password` | `string(255)` | The hashed password (including the salt and cost function identifier) of the user. |
| `deleted_at` | `timestamp` | The time when this record was deleted (when using soft deletes). |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `roles`

This table contains [user roles](/users/access-control#Overview).

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `slug` | `string(255)` | A unique text identifier for the role, to be used in URLs and other programmatic contexts. Only `[a-zA-Z0-9_-]` characters are allowed. |
| `name` | `string(255)` | Name of the role.  Any characters can be used. |
| `description` | `text` | A brief description of the role and its purpose. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `permissions`

This table contains [user permissions](/users/access-control#Definingpermissions).

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `slug` | `string(255)` | The referencing identifier for the permission.  Does **not** need to be unique.  Only `[a-zA-Z0-9_]` characters are allowed. |
| `name` | `string(255)` | Name of the permission.  Any characters can be used. |
| `conditions` | `text` | The [conditions](/users/access-control#Accessconditions) on which this permission should be evaluated. |
| `description` | `text` | A brief description of the permission and its purpose. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `activities`

This table serves as the default storage method for [user activity logs](/users/activity-logging).

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `user_id` | unsigned `int` | The `user_id` of the user who completed this activity. |
| `ip_address` | `string(45)` | The IP address of the user when they completed this activity. |
| `type` | `string(255)` | An identifier used to track the [type](/users/activity-logging#Defaultactivitytypes) of activity. |
| `occurred_at` | `timestamp` | The time when the activity was completed. |
| `description` | `text` | A description of the activity. |

### `groups`

This table contains records for each [user group](/users/groups).

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `slug` | `string(255)` | A unique text identifier for the group, to be used in URLs and other programmatic contexts. Only `[a-zA-Z0-9_-]` characters are allowed. |
| `name` | `string(255)` | Name of the group.  Any characters can be used. |
| `description` | `text` | A brief description of the group and its purpose. |
| `icon` | `string(100)` | CSS classes identifying the icon to represent users in this group. For example, `fa fa-user`. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `password_resets`

This table contains records for each [password reset request](/users/user-accounts#Passwordresetrequests) that is issued.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `user_id` | unsigned `int` | The `user_id` of the user who requested the password reset. |
| `hash` | `string(255)` | The secret token, emailed to the user, that they must present to complete the reset. |
| `completed` | `bool` | Flags whether or not the reset was completed successfully. |
| `expires_at` | `timestamp` | The time when this request will expire, after which the user will need to submit a new request. |
| `completed_at` | `timestamp` | The time when this request was completed. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `verifications`

This table contains records for [new account verification](/users/user-accounts#account-verification) tokens.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `user_id` | unsigned `int` | The `user_id` of the user to be verified. |
| `hash` | `string(255)` | The secret token, emailed to the user, that they must present to complete the verification. |
| `completed` | `bool` | Flags whether or not the verification was completed successfully. |
| `expires_at` | `timestamp` | The time when this request will expire, after which the user will need to resend the verification request. |
| `completed_at` | `timestamp` | The time when this request was completed. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `persistences`

This table stores records for recreating expired user sessions from a "Remember me" token.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `id` | `string` | The unique identifier for the record. |
| `user_id` | unsigned `int` | The `user_id` of the user to be persistently authenticated. |
| `token` | `string(40)` | The token that the user must present to recreate their session. |
| `persistent_token` | `bool` | The [series identifier](http://jaspan.com/improved_persistent_login_cookie_best_practice) for the user's persistent session. |
| `expires_at` | `timestamp` | The time when the persistent session will expire, and the user will have to reauthenticate. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `role_users`

This table maps users to roles.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `user_id` | unsigned `int` | The `user_id` of the user to be associated with the role. |
| `role_id` | unsigned `int` | The `role_id` of the role to be associated with the user. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |

### `permission_roles`

This table maps roles to permissions.

| Column | Type | Description |
| ------ | ---- | ----------- |
| `role_id` | unsigned `int` | The `role_id` of the role to be associated with the permission. |
| `permission_id` | unsigned `int` | The `permission_id` of the permission to be associated with the role. |
| `created_at` | `timestamp` | The time when this record was created. |
| `updated_at` | `timestamp` | The time when this record was last updated. |
