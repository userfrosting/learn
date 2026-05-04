---
title: RESTful Endpoints
metadata:
    description: Together, a specific url and method are commonly referred to as an **endpoint**.  It is important to use a consistent, RESTful approach to the URLs and methods you choose for each endpoint.
taxonomy:
    category: docs
---

A RESTful url should represent a _thing_, not an _action_. We want to avoid putting any verbs in the name of the url. Instead, the action should be defined by the HTTP method. For example:

```
// Bad
GET http://example.com/get-users
GET http://example.com/update-user?name=bob

// Good
GET http://example.com/users
PUT http://example.com/users/u/bob
```

Of course, a "resource" can just as easily be a more abstract concept, like a component of a web page:

```
GET http://example.com/modals/users/create
```

This might seem to contradict what we just said, which is not to use verbs in our urls. However in this case, "create" isn't referring to the client's action, but to the _resource itself_. We're **getting** the "create user" modal, so `modals/users/create` is the resource name, and our action is `GET`.

## Naming Scheme

UserFrosting uses a specific naming scheme for endpoints, which we would encourage you to stick with when you start to develop your own endpoints.

Here's an example of routes used by UserFrosting :

| Method | Url                      | Description                                                 |
| ------ | ------------------------ | ----------------------------------------------------------- |
| GET    | `/users`                 | Load an admin page that contains a list of users            |
| GET    | `/users/u/bob`           | Load an admin page that contains a single user              |
| GET    | `/api/users`             | Get a list of users and their information, as a JSON object |
| GET    | `/api/users/u/bob`       | Get a single user's information as a JSON object            |
| POST   | `/api/users`             | Create a new user                                           |
| PUT    | `/api/users/u/bob`       | Update an existing user                                     |
| PUT    | `/api/users/u/bob/email` | Update specific field for an existing user                  |
| DELETE | `/api/users/u/bob`       | Delete an existing user                                     |
| DELETE | `/api/users`             | Delete all users                                            |
| GET    | `/modals/users/edit`     | Get an "edit user" modal HTML fragment                      |


[notice=note]Notice that some requests use the exact same url, and only differ in the HTTP method used. For example, `/api/users/u/bob` can be used to retrieve, update, or delete Bob's account depending on which HTTP verb we are using.[/notice]

[notice=tip]The `route:list` [Bakery Command](/cli/commands#route-list) will display the full list of provided routes.[/notice]