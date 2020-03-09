---
title: Internationalization
metadata:
    description: Internationalization consists of the translation files used to translate pages of your web application. UserFrosting provides a framework for translating strings and sentences easily and efficiently.
taxonomy:
    category: docs
---

#### Chapter 16

# Internationalization

UserFrosting comes with a complete internationalization system. This system allows you to translate your pages in any language you want. The internationalization service uses the [i18n module](https://github.com/userfrosting/i18n) to handles translation tasks for UserFrosting. The internationalization system also includes a powerful pluralization handling.

Translating strings, or sentences, is as easy as assigning localized sentences to a common _translation key_. To achieve this, two things are used: the translation files and the `Translator`.
