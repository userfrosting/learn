---
title: RESTful Endpoints
metadata:
    description: Together, a specific url and method are commonly referred to as an **endpoint**.  It is important to use a consistent, RESTful approach to the URLs and methods you choose for each endpoint.
taxonomy:
    category: docs
---

## RESTful Endpoints

A RESTful url should represent a _thing_, not an _action_.  We want to avoid putting any verbs in the name of the url - instead, the action should be defined by the HTTP method.  For example:

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

This might seem to contradict what we just said, which is not to use verbs in our urls.  However in this case, "create" isn't referring to the client's action, but to the _resource itself_.  We're **getting** the "create user" modal, so `modals/users/create` is the resource name, and our action is `GET`.

### Naming Scheme

UserFrosting uses a specific naming scheme for endpoints, which we would encourage you to stick with when you start to develop your own endpoints.

**Load an admin page that contains a list of users**

`GET /admin/users`

**Load an admin page that contains a single user**

`GET /admin/users/u/bob`

**Get a list of users and their information, as a JSON object**

`GET /api/users`

**Get a single user's information as a JSON object**

`GET /api/users/u/bob`

**Create a new user**

`POST /api/users`

**Update an existing user**

`PUT /api/users/u/bob`

**Update specific field for an existing user**

`PUT /api/users/u/bob/email`

**Delete an existing user**

`DELETE /api/users/u/bob`

**Delete all users**

`DELETE /api/users`

**Get an "edit user" modal HTML fragment**

`GET /modals/users/edit`

>>>>> Notice that some requests use the exact same url, and only differ in the HTTP method used.  For example, `/api/users/u/bob` can be used to retrieve, update, or delete Bob's account depending on which HTTP verb we are using.
