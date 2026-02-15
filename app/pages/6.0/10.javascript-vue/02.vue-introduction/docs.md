---
title: Vue Framework
description: Learn how Vue 3 powers modern UserFrosting applications and why it replaced jQuery for building interactive user interfaces.
---

If you're coming from older versions of UserFrosting or traditional server-rendered applications, [Vue 3](https://vuejs.org/) represents a significant shift in how you build user interfaces. Let's explore what Vue is, why it matters, and how UserFrosting leverages it.

## The Evolution from jQuery to Vue

### The jQuery Era

For many years, jQuery was the go-to solution for adding interactivity to web pages. It made DOM manipulation easier, handled browser inconsistencies, and provided a simple API for common tasks. In earlier versions of UserFrosting, jQuery powered most frontend interactions—handling form submissions, updating page content, and managing UI components.

However, as web applications grew more complex, jQuery's approach showed its limitations:

- **Imperative code** - You tell the browser exactly how to update the DOM, step by step
- **Manual state management** - Keeping track of application state required careful coordination
- **DOM-centric** - Everything revolves around querying and manipulating DOM elements
- **Difficult testing** - Business logic mixed with DOM manipulation made testing challenging
- **No component system** - Reusing UI patterns required copying and pasting code

### The Modern Framework Approach

Modern frameworks like Vue take a fundamentally different approach called **declarative programming**. Instead of telling the browser how to update the page, you describe what the page should look like based on your data, and the framework handles the updates automatically.

This shift brings several advantages:

- **Reactive data** - When your data changes, the UI updates automatically
- **Component-based** - Build reusable, self-contained UI components
- **Cleaner code** - Separate concerns between data, logic, and presentation
- **Better testability** - Business logic is independent of DOM manipulation
- **Developer experience** - Tools, debugging, and ecosystem designed for modern web development

## What is Vue?

Vue (pronounced "view") is a progressive JavaScript framework for building user interfaces. Created by Evan You and now maintained by a large community, Vue focuses on being approachable, performant, and versatile.

### Core Concepts

#### Reactive Data System

Vue's heart is its reactivity system. When you change data, Vue automatically updates the parts of your page that depend on that data. No more manually querying elements and updating their content—just change the data, and Vue handles the rest.

Think of it like a spreadsheet: when you change a cell, all formulas referencing that cell update automatically. Vue brings this same automatic dependency tracking to your web application.

#### Component-Based Architecture

Everything in Vue is a component—a self-contained piece of UI with its own data, logic, and template. Components can be as simple as a button or as complex as an entire application. They can be nested, reused, and composed together to build complex interfaces.

Components make your code:
- **Reusable** - Write once, use everywhere
- **Maintainable** - Each component manages its own concerns
- **Testable** - Test components in isolation
- **Shareable** - Components can be packaged and distributed

#### Template Syntax

Vue uses an HTML-based template syntax that lets you declaratively describe how your UI should look. You can bind data to text, attributes, and HTML structure using simple directives. The templates are valid HTML that can be parsed by browsers and HTML tools.

#### Single File Components

Vue's signature feature is Single File Components (SFCs)—files with a `.vue` extension that combine template, JavaScript logic, and styles in one place. This colocation makes it easy to understand everything a component does without jumping between files.

### What Vue Does

Let's look at common web application tasks and how Vue handles them:

- **Displaying Dynamic Data**: Instead of selecting elements and updating their text content, you bind data directly in your template. When the data changes, the UI updates automatically.
- **Handling User Input**: Vue provides two-way data binding, meaning form inputs automatically stay synchronized with your data. No need to manually read input values or update them when data changes.
- **Conditional Rendering**: Show or hide elements based on conditions using simple directives. Vue efficiently adds or removes elements from the DOM as conditions change.
- **List Rendering**: Display arrays of data as lists. Vue efficiently updates only the changed items when your array changes, rather than re-rendering the entire list.
- **Event Handling**: Attach event listeners declaratively in your template. Vue provides modifiers for common tasks like preventing default behavior or stopping event propagation.
- **Computed Properties**: Derive values from your data that update automatically when dependencies change. Think of them as formulas in a spreadsheet—define once, and they recalculate automatically.
- **Lifecycle Hooks**: Run code at specific points in a component's life—when it's created, mounted to the DOM, updated, or destroyed. This lets you integrate with third-party libraries, fetch data, or clean up resources.
- **State Management**: For complex applications, Vue's official state management library (Pinia) provides a centralized store for application-wide state. This makes it easy to share data between components and manage complex state logic.

## Vue in UserFrosting

Now that you understand what Vue is, let's see how UserFrosting leverages it to create powerful, interactive applications. As described on the previous page, UserFrosting provides a hybrid architecture:

- **PHP backend** - Handles all server-side logic : authentication, database operations, etc.
- **Vue frontend** - Handle all UI logic : Powers interactive user interfaces, client-side validation, and dynamic interfaces
- **API-based communication** - Handle the communication between the two : Frontend and backend communicate through HTTP APIs (typically JSON)

This separation provides flexibility: your frontend can be a single-page application (SPA), a traditional (Twig) server-rendered app with Vue islands, or anything in between.

#### Full Page Applications

Some parts of the frontend can be complete Vue applications. In this case, multiple pages are completely controlled by Vue, with routing handled by Vue Router. The data displayed is fetched from the backend via API calls. This approach can be ideal for admin dashboards, complex forms, or interactive data management interfaces.

The default UserFrosting admin interface and pages are built as a full-page Vue application. When you access a fresh installation of UserFrosting in your browser, you're interacting with a Vue app that communicates with the backend via APIs.

> [!IMPORTANT]
> In this setup, the initial page load is handled by PHP and Twig, which **ONLY** render the basic HTML structure. Once the page loads, Vue takes over and manages all subsequent interactions and provides all subsequent template rendering. This means Twig is responsible only for rendering the initial HTML—**all other templates are defined as Vue components**!

#### Vue Islands

Other pages can be traditional server-side rendering (Twig templates) but include Vue components for specific interactive features. For example, a blog page might be mostly static but use a Vue component for a comment form or real-time preview.

> [!NOTE]
> While the default UserFrosting installation doesn't use Vue Islands, you can choose to use traditional Twig template with Vue islands in your own custom pages. This allows you to add interactivity to specific parts of your page without converting the entire page into a Vue app. 
> 
> However, for new applications, we recommend embracing the full-page Vue approach for a more modern and consistent development experience, as well as direct integration with the Admin interface.

### Key Vue Features Provided by UserFrosting

The UI that's is included in a default UserFrosting installation is built as a set of Vue components, provided by each sprinkles. 

UserFrosting's **Core Sprinkle** provides Vue components and utilities that form the foundation of the frontend:
- **Full Translator** - Vue components and composables for working with UserFrosting's translation system
- **Base components** - Common helper composable to handle CSRF, Sprunjer, etc.
- **Fortress integration** - Vue components for working with UserFrosting's validation system
- **Router configuration** - Pre-configured Vue Router setup
- **Store modules** - Pinia stores for common application state : Alerts, Configuration, etc.

The **Account Sprinkle** builds on Core with authentication-related components:
- **Login/registration forms** - Complete authentication flows
- **User management** - Components for viewing and editing users
- **Permission interfaces** - UI for managing roles and permissions
- **Profile components** - User profile viewing and editing

The **Admin Sprinkle** extends Account with administrative components and interfaces. It provides the admin dashboard you see when you log into a fresh UserFrosting installation.

Finally, the **Pink Cupcake** theme provides the frontend theme. It include:
- **Custom styling** - Custom styles based on UiKit
- **Layout customization** - Overall page structure and navigation
- **Component variants** - Provide themed versions of core components

These sprinkles serve as both a working theme and a reference for creating your own custom themes and components. You can use them as-is, customize them, or use them as a template for building your own frontend from scratch.

The following chapters will dive deeper into how to use them in your UserFrosting application, including how to customize the default components, build your own Vue components, and integrate them with the backend API.

## Learning Vue

Don't worry if Vue is new to you—it's designed to be approachable, and the next chapters will guide you through the essentials. Meanwhile, if you want to learn more about Vue right now, here are resources to help you learn:

- [Vue 3 Documentation](https://vuejs.org/) - Comprehensive guides and API reference
- [Vue Tutorial](https://vuejs.org/tutorial/) - Interactive step-by-step tutorial
- [Vue Examples](https://vuejs.org/examples/) - Common patterns and use cases
- [Vue School](https://vueschool.io/) - Video courses from beginner to advanced

## Next Steps

Now that you understand Vue's role in UserFrosting, you're ready to learn how 
