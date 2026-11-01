---
title: Frontend Testing with Vitest
description: Learn how to test Vue components, TypeScript code, and frontend logic using Vitest in UserFrosting.
---

While PHPUnit handles backend testing, UserFrosting uses **Vitest** for testing frontend code — Vue 3 components, TypeScript modules, composables, stores, and more. Vitest is a blazing-fast test runner built on Vite, offering native ESM support, instant Hot Module Replacement (HMR), and seamless TypeScript integration.

## What is Vitest?

[Vitest](https://vitest.dev/) is a modern testing framework designed specifically for Vite-powered projects. It provides:

- **Lightning-fast execution** with smart watch mode
- **Native ESM and TypeScript support** — no transpilation needed
- **Jest-compatible API** — familiar syntax if you've used Jest before
- **Vue Test Utils integration** — for testing Vue components
- **Built-in code coverage** via c8/istanbul
- **Happy DOM / jsdom** for DOM simulation

## Configuration

Vitest is configured in your [vite.config.ts](vite.config.ts). Here's a typical setup:

```ts
/// <reference types="vitest" />
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    test: {
        coverage: {
            reportsDirectory: './_meta/_coverage',
            include: ['app/assets/**/*.*'],
            exclude: ['app/assets/tests/**/*.*']
        },
        environment: 'happy-dom', // or 'jsdom'
        setupFiles: ['app/assets/tests/setup.ts']
    }
})
```

**Key Options:**
- **`environment`**: Use `'happy-dom'` (faster, lightweight) or `'jsdom'` for DOM simulation
- **`coverage`**: Configure code coverage reporting
- **`setupFiles`**: Global setup scripts that run before all tests
- **`exclude`**: Patterns to exclude from coverage (like test files themselves)

## Test Organization

Place your tests in a `tests/` directory next to your source code:

```
app/
  assets/
    components/
      MyComponent.vue
    composables/
      useMyFeature.ts
    stores/
      myStore.ts
    tests/
      components/
        MyComponent.test.ts
      composables/
        useMyFeature.test.ts
      stores/
        myStore.test.ts
      setup.ts  # Global test setup
```

Test files should use the `.test.ts` or `.spec.ts` extension.

## Writing Basic Tests

Vitest provides a Jest-compatible API with `describe`, `test`, `expect`, and more:

```ts
import { describe, test, expect } from 'vitest'

describe('Math utilities', () => {
    test('addition works correctly', () => {
        expect(1 + 1).toBe(2)
    })

    test('multiplication works correctly', () => {
        expect(2 * 3).toBe(6)
    })
})
```

**Common Matchers:**
- **`toBe(value)`**: Strict equality (`===`)
- **`toEqual(value)`**: Deep equality for objects/arrays
- **`toMatch(pattern)`**: String or regex matching
- **`toContain(item)`**: Array or string contains item
- **`toBeTruthy()` / `toBeFalsy()`**: Truthy/falsy checks
- **`toHaveBeenCalled()`**: Mock function was called
- **`toHaveBeenCalledWith(...args)`**: Mock was called with specific arguments

## Running Tests

Run all tests:

```bash
npm run test
```

Run tests with coverage:

```bash
npm run coverage
```

Generate a coverage report (typically in `_meta/coverage/`):

```bash
npm run coverage
open _meta/coverage/index.html
```

> [!NOTE]
> Test commands are defined in your `package.json`. UserFrosting typically uses `"test": "vitest run"` and `"coverage": "vitest run --coverage"`.

## What's Next

Explore the following topics to master frontend testing in UserFrosting:

- **[Testing Vue Components](/testing/frontend-testing/vue-components)**: Learn how to test Vue components with Vue Test Utils, including props, slots, events, and data attributes
- **[Mocking & Stubbing](/testing/frontend-testing/mocking)**: Master mocking techniques for modules, Pinia stores, Vue Router, and global properties
- **[Advanced Testing](/testing/frontend-testing/advanced)**: Test composables, Pinia stores, handle async code, and use lifecycle hooks
- **[Best Practices](/testing/frontend-testing/best-practices)**: Follow proven patterns and see a complete example of a well-tested component

## Resources

- **[Vitest Documentation](https://vitest.dev/)** — Official Vitest docs
- **[Vue Test Utils](https://test-utils.vuejs.org/)** — Testing library for Vue 3
- **[Testing Library](https://testing-library.com/)** — Alternative testing approach focused on user behavior
- **[Pinia Testing](https://pinia.vuejs.org/cookbook/testing.html)** — Testing Pinia stores
