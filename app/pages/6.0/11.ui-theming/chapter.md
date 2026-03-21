---
title: UI Framework and Theming
description: Build a practical app UI with UserFrosting, Pink-Cupcake, and UIkit from entry file to theme overrides.
---

#### Chapter 11

# UI Framework and Theming

You've learned how to build Vue components and wire up API calls in Chapter 10. Now it's time to bring all of that together into a real, usable app interface — the kind your users will actually see, click, and interact with every day.

UserFrosting ships with **Pink-Cupcake**, an official Vue theme package that gives you a complete app shell right out of the box: navbar, sidebar, page headers, alerts, data table components, modals, and more. Instead of building all of that from scratch, you compose it, customize it, and extend it.

Behind Pink-Cupcake is **UIkit**, a lightweight CSS framework that handles responsive layout, spacing, typography, and visual utilities. Both work together: Pink-Cupcake gives you the high-level structure, UIkit handles the fine-grained styling details.

Your app UI is built from three layers:

1. **Vue pages and routes** — the structure and interactive logic of each page.
2. **Pink-Cupcake components** — the pre-built layout shell and reusable UI building blocks.
3. **UIkit classes and LESS variables** — the design foundation for colors, spacing, and visual theme.

This chapter walks you through the complete workflow: understand the file structure, use layout components, build forms, add new pages with navigation, handle user feedback, query server data with Sprunjes, and finally customize the theme to make the app your own.

> [!IMPORTANT]
> This chapter assumes you are comfortable with Vue Router basics from [Chapter 10: Vue Framework](/javascript-vue/vue). If not, read that first and then come back.
