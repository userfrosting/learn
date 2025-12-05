---
title: Your Application
metadata:
    description: A Sprinkle can contain assets, configuration files, translations, routes, PHP classes, and Twig templates.
taxonomy:
    category: docs
---

UserFrosting 4 introduced the **Sprinkle system** as a way to completely isolate the code and content that you and your team produce from the core UserFrosting installation. **UserFrosting 5** takes this concept a step further, requiring a new chapter on the basic UserFrosting project structure before even talking about downloading any code!

It's important to understand how UserFrosting 5 is structured. This will be key to understand the installation process, the tools required to do so, and how all the parts fits together to create your own project.

If you're familiar with UserFrosting 4, most of your code used to live along UserFrosting's code. Parts where separated in **Sprinkles**, but everything was located in your project folder. This meant if you were to host your project on Github, most of UserFrosting's code was also hosted in your repo. This was fine, but decreased modularity and made upgrades more difficult.

To make things easier, UserFrosting 5 now separates all of your code from UserFrosting code. UserFrosting code is now handled by Composer. This is where the **App Skeleton** comes in.

## The App Skeleton, your project's template

The **app skeleton** is a bare-bone UserFrosting project. Think of it like a starting kit, or template, to create your own application. Everything in the skeleton is meant to be modified. As such, the skeleton doesn't need to be a synced copy of the UserFrosting Github repository (called a ***fork***). It provides example pages and all the basic configuration to run a default UserFrosting application. 

> [!IMPORTANT]
> While there is an official UserFrosting App Skeleton, it doesn't need to be the only one. Many skeletons could exist as starting points for new UserFrosting-based projects

But what makes a UserFrosting application, a UserFrosting application? What does it contain? Well, it's not much different than a normal modern PHP application. Your UserFrosting based project will consist of your code, plus a bunch of **dependencies**. These dependencies are all handled by Composer (which we'll explain later) and are themselves separated into three groups : **The Framework**, **External Libraries**, and **Sprinkles** :

```
Your Project
├── Your code & content
└── Dependencies
    ├── UserFrosting Framework
    ├── External Libraries
    └── Sprinkles
```

The next pages will explain which dependencies UserFrosting relies on, what is the UserFrosting Framework, what are sprinkles, and which sprinkles are bundled with UserFrosting by default.
