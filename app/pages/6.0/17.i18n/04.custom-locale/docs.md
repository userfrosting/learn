---
title: Creating a custom locale
metadata:
    description: TODO
taxonomy:
    category: docs
---

Whether to add an unsupported language (Klingon anyone?), to create a new dialect (eg. French Canadian), or simply to modify the predefined values, a custom locale can help you communicate with your end users.

Two things required to make a new locale available in UserFrosting. The first it to create a **locale configuration file**. The second is to add your locale identifier to the available locale config. While the second part should be pretty straitfoward at this point, the locale file has some require information it need to provide for your new locale to work.

These information need to be stored in a `locale.yaml` file, located in the locale folder and accessible accessible using the `locale://XX_YY/locale.yaml` URI, where `XX_YY` is your locale **language-country** code.

The configuration file can contain multiple options. For example, to create a French Canadian (fr_CA) locale :

```yaml
name: French Canadian
regional: Français Canadien
authors:
  - Foo Bar
  - Bar Foo
plural_rule: 2
parents:
  - fr_FR
```

## Possible values

### name

The name of the locale. Should be the English version of the name.

### regional

The localized name of the locale. For example, for the French locale, the name of the locale in French.

### authors

A list of authors for the locale.

### plural_rule

The plural rule number associated with the locale. See [Pluralization](/i18n/latranslator#pluralization) for more details.

### parents

A list of parents locales for this locale. Each locale configuraton entries will be loaded on top of the parents one, including all dictionary definitions.

For example, if the `fr_CA` locale has `fr_FR` as parent, all config and all keys not found in the `CA` translation will fallback to the `FR` one. If the `fr_FR` locale also has `en_US` as parent itself, all keys not found in `CA` and `FR` will fallback to the English values.

It is recommended all locale have at least `en_US` as a top parent, so undefined keys in your locale will fallback to the English version.
