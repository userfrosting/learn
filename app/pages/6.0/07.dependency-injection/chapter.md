---
title: Dependency Injection
description: Services are a way to allow objects that perform specific, commonly used functions to be reused throughout your application. Mail, logging, and authorization are all examples of services.
wip: true
---

#### Chapter 7

# Dependency Injection

Dependency injection is one of the fundamental pillars of modern object-oriented software design. It is used extensively throughout UserFrosting to glue all services together while maintaining great flexibility to extend the basics functionalities of UserFrosting to create your own project. 

Services are a way to allow objects that perform specific, commonly used functions to be reused throughout your application. Mail, logging, and authorization are all examples of services. The **Dependency Injection (DI) Container** provides an elegant and loosely coupled way to make various services available globally in your application.
