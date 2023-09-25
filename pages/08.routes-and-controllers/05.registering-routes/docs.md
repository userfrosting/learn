---
title: Registering Routes
metadata:
    description: The front controller consists of the route definitions that UserFrosting uses to process incoming requests from the client.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)


## Overriding Routes

Routes themselves cannot be extended by other Sprinkles and they cannot be overridden. To modify the behavior of one of the routes that ships with UserFrosting, you may simply redefine it in one of your route files. This definition will replace any routes for the endpoint defined in previously loaded Sprinkles.