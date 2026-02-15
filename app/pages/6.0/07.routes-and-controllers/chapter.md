---
title: Routes and Controllers
description: UserFrosting controllers are used to mediate interactions between the model and view, and are responsible for much of your application's logic.
---

#### Chapter 7

# Routes and Controllers

Every web application needs to answer a fundamental question: **when a user visits a URL or submits a form, what code should run?** Without a structured approach, you end up with spaghetti code mixing database queries, business logic, and HTML generation in confusing, unmaintainable ways.

UserFrosting uses the **MVC (Model-View-Controller)** pattern with RESTful routing to cleanly separate concerns. **Routes** map URLs to **controllers**, which contain your application logic and coordinate between **models** (data) and **views** (presentation). This separation makes your code easier to understand, test, and maintain.

This chapter covers RESTful API design, the front controller pattern, creating controllers for your features, and securely handling user input. You'll learn to build well-structured endpoints that are both powerful and maintainable.
