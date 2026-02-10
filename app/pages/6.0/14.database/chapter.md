---
title: Database
description: UserFrosting uses Laravel's Eloquent component to provide a convenient abstraction layer on top of your database.
---

#### Chapter 14

# The Database and ORM Layer

Every application needs to store data—user accounts, content, settings, and more. But writing raw SQL queries for every database operation leads to repetitive code, security vulnerabilities (like SQL injection), and maintenance headaches.

UserFrosting uses Laravel's [Eloquent ORM](https://laravel.com/docs/10.x/eloquent) to solve these problems. An **ORM (Object-Relational Mapper)** lets you work with database records as PHP objects, automatically handling queries, relationships, and data validation. Instead of writing SQL, you write intuitive PHP code that's easier to read, test, and secure.

This chapter covers everything you need to work with databases in UserFrosting: defining models, querying data, managing relationships, creating migrations to version your schema, and seeding initial data. You'll learn to build robust, maintainable data layers for your applications.
