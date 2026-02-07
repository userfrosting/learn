---
title: Dependency Injection
description: Services are a way to allow objects that perform specific, commonly used functions to be reused throughout your application. Mail, logging, and authorization are all examples of services.
---

#### Chapter 7

# Dependency Injection

Modern applications need shared functionality—sending emails, logging events, authorizing users. But how do you make these services available throughout your code without creating tight coupling and making your code untestable?

**Dependency Injection (DI)** is the solution. Instead of objects creating their own dependencies, they declare what they need, and a **DI Container** provides them. This makes code modular, testable, and flexible—you can easily swap implementations without changing dependent code.

UserFrosting uses dependency injection extensively to wire together services like mail, logging, database access, and authorization. Understanding the DI container is key to extending UserFrosting's functionality and building well-architected applications.

This chapter explains dependency injection concepts, how UserFrosting's DI container works, the services available by default, and how to add or customize services for your own needs.
