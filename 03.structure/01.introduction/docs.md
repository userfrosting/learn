---
title: Your Application
metadata:
    description: A Sprinkle can contain assets, configuration files, translations, routes, PHP classes, and Twig templates.
taxonomy:
    category: docs
---
<!-- TODO : Review this page -->

While UserFrosting 4 introduced the **Sprinkle system** as a way to completely isolate the code and content that you and your team produce from the core UserFrosting installation, **UserFrosting 5** take this concept a step further. So much, that this chapter was added to go through the basic structure of a UserFrosting application, before even talking about downloading any code!

It's important to understand how UserFrosting 5 is structured, as this will be key to understand the installation process, the tool required to do so and how all the parts fits together to create your own application.

If you're familiar with UserFrosting 4, most of your code used to live along UserFrosting's code. Parts where separated in **Sprinkles**, but everything was integrated into the main app directory, and downloaded at once. This meant if you where to host you code on Github, all of UserFrosting's code was also hosted in your repo. This was fine, but at the cost of modularity and making upgrades more difficult.

To make things easier, UserFrosting 5 now separate all of you code from UserFrosting code. This is where the **App Skeleton** comes in.

## The App Skeleton  

The **app skeleton** is a bare-bone UserFrosting application. Think of it like a starting kit to create your own application, a template. Everything in the skeleton is meant to be modified. As such, the skeleton doesn't need to be a synced copy of the UserFrosting Github repository (called a ***fork***). It provided example pages and all the basic configuration to run a default UserFrosting application. 

But what makes a UserFrosting application, a UserFrosting application? What does it contain? Well, it's not much different than a normal modern PHP application. You UserFrosting based application will consist of your code, plus a bunch of **dependencies**. Theses dependencies are all handled by Composer (which we'll explain later) and are themselves separated in three groups : **The Framework**, **External Libraries** and **Sprinkles** :

```
You App
├── Your code & content
└── Dependencies
    ├── Framework
    ├── External Libraries
    └── Sprinkles
```

The next pages will explain which dependencies UserFrosting rely on, what is the UserFrosting Framework, what are sprinkles and which are bundled with UserFrosting by default.
