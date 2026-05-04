---
title: Future Roadmap
metadata:
    description: 
taxonomy:
    category: docs
---

## How will future update be done?

You may be thinking at this point, how easy will it be to update to future version? The new structure has been created to make this easier. With UserFrosting 4, your Sprinkle where typically a fork of the whole UserFrosting project. This meant upgrading involved a complex git command to fetch upstream updates.

With UserFrosting 5, updating should be as simple as doing `composer update`. 

- Revision versions (5.0.x) will require only `composer update` and `php bakery bake`;
- Minor versions (5.x) will require `composer update`, `php bakery bake`, and optional upgrade tasks in an upgrade page;
- Major version (6.0) will have a dedicated upgrade guide;

Our commitment for UserFrosting 5 is for all bundled sprinkles (Core, Account, Admin and AdminLTE) and the Framework to follow the same "minor" version number. They might not have the same revision, but assuming you're starting with the "5.1" skeleton, you'll have "5.1" version of bundled sprinkle as well as the framework.

## The Roadmap

Now that UserFrosting 5 has been release, what does the future hold for the UserFrosting community? Here's a preview of what's planned for future versions. As always, your help and support is greatly appreciated. If you want to get involved in the future of UserFrosting, this list should help you get started with topic that you can get involved with.

### UserFrosting 5.0.x

The main focus of UserFrosting 5.0.x is to squash some bugs, and improve the code quality.

 - Address all "todo" inside the code;
 - Fix bugs in the login
 - Etc.

### UserFrosting 5.1

The main focus of UserFrosting 5.1 is :

- Officially add PHP 8.3 support, drop PHP 8.1
- Update locales (translations), move them to their custom sprinkle
- Update Fontawesome
- 100% test coverage on the core Sprinkle;
- Improve Docker support
- Remove Assets in Framework
- Update Laravel
- Add automated test on MariaDB
- Etc.

### UserFrosting 6

Yes, UserFrosting 6 is already being planned! While UF5 goal was to completely rewrite the PHP backend, UF6 goal will be to completely rewrite the frontend !

- Brand new custom theme, replacing AdminLTE, based on [UIKit](https://getuikit.com)
- Replacing Handlebar and every frontend javascript code with [Vue.js](https://vuejs.org)

The best part is UserFrosting 5 introduced the necessary tools to make this transition works. It could in fact be entirely possible to create the new frontend on UserFrosting 5 as an optional sprinkle! However, the goal for UserFrosting 6 is to make this new frontend the default one.

You can find a proof of concept of this new Vue.js based UI (built in UF5 beta) here : [https://github.com/userfrosting/demo-vue/tree/main](https://github.com/userfrosting/demo-vue/tree/main)
