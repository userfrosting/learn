---
title: Forms and Validation
description: Build validated forms using Regle, UIkit form classes, and UserFrosting's schema adapter.
---

Most applications include some sort of interactivity in the form of... well... forms — creating a resource, editing a profile, submitting a setting. A form without validation feedback feels broken even when nothing catastrophic happened. A form that highlights the wrong field on submit, or silently fails, loses the user's trust immediately.

UserFrosting uses **Regle** ([`@regle/core`](https://www.npmjs.com/package/@regle/core)) as its standard frontend form validation library. Regle is a TypeScript-first validation library built specifically for Vue 3 reactivity. It integrates cleanly with `ref()` form state, validates on demand, and gives you per-field error messages without any magic.

## The Core Concept

Regle works by pairing a reactive form data object with a set of rules. You call `useRegle()` with both, and get back `r$` — the validation instance you use everywhere in your template.

```ts
import { useRegle } from '@regle/core'
import { required, minLength, email } from '@regle/rules'

const formData = ref({ name: '', email: '' })

const { r$ } = useRegle(formData, {
    name: { required, minLength: minLength(3) },
    email: { required, email }
})
```

That's it. `r$` is now your window into the validation state of every field.

## The `r$` Validation Instance

Once you have `r$`, three things become available per field:

| Expression | Type | Description |
|---|---|---|
| `r$.fieldName.$error` | `boolean` | `true` if the field has failed validation after `$validate()` was called |
| `r$.$errors.fieldName` | `string[]` | Array of error message strings for that field |
| `r$.$validate()` | `Promise<{ valid: boolean }>` | Trigger full validation and return whether everything passed |
| `r$.$error` | `boolean` | `true` if any field has an error — useful for disabling the submit button |

## Displaying Errors

Pink-Cupcake ships a `UFFormValidationError` component that renders a list of error messages for a field. Pass it the `r$.$errors.fieldName` array:

```vue
<div class="uk-margin">
    <label class="uk-form-label" for="name">Name</label>
    <div class="uk-form-controls">
        <input
            id="name"
            v-model="formData.name"
            class="uk-input"
            :class="{ 'uk-form-danger': r$.name.$error }"
        />
        <UFFormValidationError :errors="r$.$errors.name" />
    </div>
</div>
```

- `:class="{ 'uk-form-danger': r$.name.$error }"` highlights the input in red when its validation fails.
- `UFFormValidationError` renders each error message below the field, in the same `uk-form-danger` color.

## A Full Form Example

Here's a complete form component — form state, validation, submit handler, API error display:

```vue
<template>
    <form class="uk-form-stacked" @submit.prevent="submit">
        <div class="uk-margin">
            <label class="uk-form-label" for="name">Display name</label>
            <div class="uk-form-controls">
                <input
                    id="name"
                    v-model="formData.name"
                    class="uk-input"
                    :class="{ 'uk-form-danger': r$.name.$error }"
                />
                <UFFormValidationError :errors="r$.$errors.name" />
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="email">Email</label>
            <div class="uk-form-controls">
                <input
                    id="email"
                    v-model="formData.email"
                    class="uk-input"
                    :class="{ 'uk-form-danger': r$.email.$error }"
                />
                <UFFormValidationError :errors="r$.$errors.email" />
            </div>
        </div>

        <UFAlert v-if="apiError" :alert="apiError" @close="apiError = null" />

        <button
            class="uk-button uk-button-primary"
            :disabled="r$.$error || isSaving"
            type="submit">
            {{ isSaving ? 'Saving...' : 'Save' }}
        </button>
    </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRegle } from '@regle/core'
import { required, minLength, email } from '@regle/rules'

const formData = ref({ name: '', email: '' })
const isSaving = ref(false)
const apiError = ref(null)

const { r$ } = useRegle(formData, {
    name: { required, minLength: minLength(2) },
    email: { required, email }
})

async function submit() {
    const { valid } = await r$.$validate()
    if (!valid) return

    isSaving.value = true
    try {
        // await yourApi.save(formData.value)
    } catch (error) {
        apiError.value = error
    } finally {
        isSaving.value = false
    }
}
</script>
```

A few patterns worth noting:

- `await r$.$validate()` triggers validation on all fields at once. Fields only show errors after this is called, so users aren't greeted with a red form before they've typed anything.
- `:disabled="r$.$error || isSaving"` blocks submission while validation has failed or an API call is in flight — preventing double submissions.
- `UFAlert` shows API-level errors (network failure, server rejection) separately from field validation errors.

## Schema-Based Validation

UserFrosting's server-side form validation is defined in **Fortress YAML schema files** — these live in your sprinkle's `schema/requests/` folder and describe every field's validators and error messages. Rewriting those same rules in TypeScript for the frontend would be redundant and fragile.

Instead, `@userfrosting/sprinkle-core` ships a `useRuleSchemaAdapter()` composable that reads a YAML schema file and converts it to a Regle-compatible rules object automatically.

> [!TIP]
> Unless you're building a quick prototype, prefer `useRuleSchemaAdapter()` over writing Regle rules by hand. Your frontend and backend validation rules stay in sync automatically, and you only have to maintain one schema file.

### Usage

```ts
import { useRegle } from '@regle/core'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/profile.yaml'

const formData = ref({ user_name: '', email: '' })

const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))
```

`adapt(schemaFile)` receives the parsed YAML object and returns a plain Regle rules object — exactly as if you had written it by hand.

> [!IMPORTANT]
> Importing YAML files in Vite requires the [`@modyfi/vite-plugin-yaml`](https://www.npmjs.com/package/@modyfi/vite-plugin-yaml) plugin, which is enabled by default in your `vite.config.ts`.

### YAML Schema Structure

A Fortress schema file defines fields, their validators, and optional translated error messages. Here's a typical example:

```yaml
user_name:
  validators:
    required:
      label: "&USERNAME"
      message: VALIDATE.REQUIRED
    length:
      label: "&USERNAME"
      min: 1
      max: 50
      message: VALIDATE.LENGTH_RANGE
    no_leading_whitespace:
      label: "&USERNAME"
      message: VALIDATE.NO_LEAD_WS
    no_trailing_whitespace:
      label: "&USERNAME"
      message: VALIDATE.NO_TRAIL_WS
    username:
      label: "&USERNAME"
      message: VALIDATE.USERNAME
email:
  validators:
    required:
      label: "&EMAIL"
      message: VALIDATE.REQUIRED
    length:
      label: "&EMAIL"
      min: 1
      max: 150
      message: VALIDATE.LENGTH_RANGE
    email:
      message: VALIDATE.INVALID_EMAIL
```

Each field maps to a `validators` object. Each validator key has an optional `message` field containing a **translation key** that `useRuleSchemaAdapter` resolves via `useTranslator()`. The `label` field (prefixed with `&` to indicate a translation alias) is available for use inside the translated message string.

Fields with `domain: server` on a validator are **server-side only** and are skipped by the adapter.

### Supported Validators

The adapter maps YAML validator keys to Regle rules from `@regle/rules`:

| YAML validator           | Regle rule                      | Notes                              |
|--------------------------|---------------------------------|------------------------------------|
| `required`               | `required`                      |                                    |
| `email`                  | `email`                         |                                    |
| `length`                 | `minLength(n)` + `maxLength(n)` | Uses `min` and/or `max` sub-fields |
| `integer`                | `integer`                       |                                    |
| `numeric`                | `numeric`                       |                                    |
| `range`                  | `between(min, max)`             | Uses `min` and `max` sub-fields    |
| `uri`                    | `url`                           |                                    |
| `regex`                  | `regex(new RegExp(...))`        | Uses a `regex` sub-field           |
| `member_of`              | `oneOf(values)`                 | Uses a `values` sub-field          |
| `not_member_of`          | `not(oneOf(values))`            | Uses a `values` sub-field          |
| `no_leading_whitespace`  | `regex(/^\S.*$/)`               |                                    |
| `no_trailing_whitespace` | `regex(/^.*\S$/)`               |                                    |
| `username`               | `regex(/^([a-z0-9.\-_])+$/i)`   |                                    |

> [!WARNING]
> The following YAML validators are **not yet implemented** in the adapter and will only log a console warning: `matches`, `equals`, `not_equals`, `not_matches`, `telephone`. Fields using these rules will only be validated server-side.

## Async Validation

Some validations can't happen client-side — like checking whether a username is already taken. Regle supports this with `createRule()`, which can accept an async validator function:

```ts
import { createRule, useRegle, type Maybe } from '@regle/core'

const usernameAvailable = createRule({
    async validator(value: Maybe<string>) {
        const res = await checkUsernameAvailability(value)
        return { $valid: res.available, $message: res.message }
    },
    message: (meta) => meta.$message
})

const { r$ } = useRegle(formData, {
    user_name: { usernameAvailable }
})
```

Regle handles the async lifecycle automatically — the field shows a pending state while the request is in flight and displays the error message if validation fails.

> [!NOTE]
> When combining schema-based rules with a custom async rule, create two separate `useRegle()` instances — one for the schema, one for the async rule — and display both sets of `$errors` for that field.

## Good Form Habits

**Always call `r$.$validate()` before submitting.** Client-side validation gives users immediate feedback without a round trip to the server.

**Show API errors separately.** Use `UFAlert` for server-level responses (unauthorized, network error, unexpected server errors) rather than trying to map them back to individual fields.

**Disable the submit button during submission.** `:disabled="r$.$error || isSaving"` covers both invalid state and in-flight requests.

**Keep UIkit form classes consistent.** Use `uk-form-stacked` for the form, `uk-form-label` for labels, `uk-input`/`uk-select`/`uk-textarea` for controls, and `uk-form-danger` on inputs with errors. This keeps all your forms visually consistent.
