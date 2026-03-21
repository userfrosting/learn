---
title: Introduction
description: Meet Pink-Cupcake — UserFrosting's pre-built theme package — and learn what you'll build in this chapter.
---

You've been building backend logic and Vue components. Now it's time to connect all of that into a real app interface — the kind with a navbar, a sidebar, forms, data tables, and a visual style that fits your project.

UserFrosting makes this easier than starting from scratch, because it ships with an official Vue theme package called **Pink-Cupcake** (`@userfrosting/theme-pink-cupcake`).

## What Is Pink-Cupcake?

Pink-Cupcake is UserFrosting's pre-built Vue theme package. It gives you a complete admin app shell without any extra assembly:

- A **navbar** with customizable links and title.
- A **sidebar** with nested navigation items and icons.
- **Page header** components for consistent page titles and breadcrumbs.
- **Alert** components for user feedback.
- **Sprunje table components** for server-driven data tables with search, filters, sorting, and pagination.
- **Modal components** for confirmations and prompts.

Think of it like moving into a well-equipped apartment rather than building one from scratch. The kitchen, the bathroom, and the living room are already there — you decide what goes where, repaint the walls to match your style, and add your own furniture where needed.

Underneath Pink-Cupcake is **UIkit**, a lightweight CSS framework that handles responsive layout, spacing, typography, and visual utilities. Both work together: you'll use Pink-Cupcake components for the high-level structure and UIkit classes for the fine-grained layout and styling details.

## What You'll Build in This Chapter

By the end of this chapter you will be able to:

- Navigate the frontend file structure and understand which file controls what.
- Use Pink-Cupcake components to compose app layout and navigation.
- Build forms with validation feedback.
- Add a brand new page with its own route, view, and navigation entries.
- Give users clear feedback with alerts and toast notifications.
- Query and display server data with Sprunjes.
- Customize the visual theme with LESS variable overrides.
