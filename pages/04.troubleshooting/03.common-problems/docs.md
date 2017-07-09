---
title: Common Problems
metadata:
    description: Commonly encountered issues when setting up, developing, or deploying a UserFrosting project.
taxonomy:
    category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).


**I get a Node/npm error when running `php bakery bake`.**

If installation of npm dependencies fails, see [npm](/basics/requirements/essential-tools-for-php#npm) to ensure npm is correctly installed and updated. You may need to [change npm permissions](https://docs.npmjs.com/getting-started/fixing-npm-permissions).

**My routes don't seem to work when I switch to `UF_MODE='production'`.**

The `production` mode, by default, enables [FastRoute's route caching](https://www.slimframework.com/docs/objects/application.html#slim-default-settings).  This can result in route definitions not being updated in the cache during production.  To resolve this, you should clear the route cache in `app/cache/routes.cache`.
