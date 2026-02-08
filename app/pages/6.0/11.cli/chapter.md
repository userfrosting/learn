---
title: Bakery CLI
description: Bakery is UserFrosting's very own command line interface (CLI) tool.
---

#### Chapter 11

# Bakery CLI

Many critical tasks need to happen outside the web request cycle—database migrations, asset compilation, cache clearing, and deployment automation can't wait for a page load or be safely exposed through a web interface.

UserFrosting provides **Bakery**, a powerful command-line interface (CLI) built on the Symfony Console component. Bakery gives you pre-built commands for database migrations, user management, asset compilation, and debugging. You can also create custom commands and extend existing ones using UserFrosting's event system.

This chapter covers [built-in commands](/cli/commands), [creating custom commands](/cli/custom-commands), and [extending commands](/cli/extending-commands).
