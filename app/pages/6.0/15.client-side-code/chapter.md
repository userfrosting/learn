---
title: Client-Side Development
description: Building modern interactive user interfaces with Vue 3, TypeScript, and UIkit
wip: true
---

#### Chapter 15

# Client-Side Development

Modern web applications are fundamentally different from the static document-based websites of the past. Today's users expect rich, interactive experiences that respond instantly to their actions—without waiting for page reloads or server round-trips.

UserFrosting 6.0 embraces this reality with a modern frontend stack centered around **Vue 3**, **TypeScript**, and **UIkit**. This chapter teaches you how to build reactive, component-based user interfaces that feel fast and responsive while integrating seamlessly with UserFrosting's backend.

## The Modern Frontend Stack

UserFrosting 6.0 uses these core technologies for client-side development:

- **Vue 3**: Progressive JavaScript framework for building reactive user interfaces
- **TypeScript**: Type-safe JavaScript that catches errors during development
- **UIkit**: Lightweight, modular CSS framework with beautiful components
- **Vite**: Lightning-fast build tool with Hot Module Replacement
- **Axios**: Promise-based HTTP client for API communication

Gone are the days of jQuery selectors and DOM manipulation. Vue 3's reactive data binding and component-based architecture make it easier to build complex UIs while writing less code.

## Why Vue 3?

If you're coming from jQuery or vanilla JavaScript, Vue 3 might seem like a significant change. Here's why it's worth learning:

**Reactive Data Binding**: Changes to your data automatically update the UI. No more manually finding elements and updating their content.

**Component-Based**: Break your UI into reusable pieces. Each component encapsulates its HTML, CSS, and JavaScript.

**TypeScript Support**: Catch bugs before they reach production with type checking and intelligent code completion.

**Better Performance**: Virtual DOM and optimized reactivity mean faster updates and smoother interactions.

**Modern Developer Experience**: Hot Module Replacement (HMR) shows your changes instantly without page reloads.

## What This Chapter Covers

This chapter is your guide to building client-side features in UserFrosting:

- **[Overview](client-side-code/overview)**: Understanding the client-side stack and available tools
- **[Exporting Variables](client-side-code/exporting-variables)**: Passing data from PHP/Twig to JavaScript
- **[Vue Components](client-side-code/vue-components)**: Building reactive components with Vue 3
- **[Forms](client-side-code/components/forms)**: Creating validated, AJAX-powered forms
- **[Tables](client-side-code/components/tables)**: Building data tables with sorting and filtering
- **[Collections](client-side-code/components/collections)**: Managing dynamic lists of items
- **[Alerts](client-side-code/components/alerts)**: Displaying notifications and messages

> [!TIP]
> If you're new to Vue 3, don't worry! This chapter explains concepts as we go. You don't need to be a Vue expert to build great features with UserFrosting.

## Learning Path

**If you're new to Vue**: Start with [Overview](client-side-code/overview) to understand the basics, then work through the component examples.

**If you know Vue 2**: The [Vue Components](client-side-code/vue-components) section highlights what's different in Vue 3.

**If you're upgrading from UserFrosting 5.1**: The component sections show how to migrate from jQuery plugins to Vue 3 patterns.

Ready to build modern, interactive user interfaces? Let's dive in!
