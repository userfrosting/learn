---
title: TypeScript
description: Advanced techniques for asset management including TypeScript, Vue 3, preprocessors, code splitting, and optimization.
---

[TypeScript](https://www.typescriptlang.org/) is a strongly typed programming language that builds on JavaScript by adding optional static type definitions. It helps catch errors during development, provides better IDE support with autocomplete and refactoring tools, and makes your code more maintainable and self-documenting.

Think of TypeScript as a safety net for your JavaScript. Your code still runs as JavaScript in the browser, but before it gets there, TypeScript checks your assumptions (for example, "this value should be a string") and warns you when something does not match.

Vite provides first-class TypeScript support with zero configuration. Simply use `.ts` files and Vite will compile them automatically.

If you're new to TypeScript, the official docs are excellent and beginner-friendly:
- [TypeScript Documentation](https://www.typescriptlang.org/docs/)
- [The Basics](https://www.typescriptlang.org/docs/handbook/2/basic-types.html)

## Why TypeScript Matters ?

Consider this JavaScript code:

```js
const message = 'Hello World!'
message() // Runtime: TypeError: message is not a function
```

If we break this down, the second line tries to call `message` directly as if it were a function, which is not valid since `message` is a string. In plain JavaScript, this code would run without any issues until it hits the second line, at which point it would throw a runtime error.

In plain JavaScript, this only fails when the code runs in the browser, which can lead to bugs that are harder to track down. These kind of bugs can even find their way into production if not caught during testing! 

In TypeScript, you would get an error immediately on build, or even in your editor as you type, by declaring the type of `message`:

```ts
const message: string = 'Hello World!'
message() // TypeError: message is not a function
```

This becomes even more powerful when you have complex data structures, functions, and components. You would guess accessing a property that doesn’t exist on an object should throw an error too. Instead, JavaScript gives us different behavior and returns the value `undefined`:

```js
const user = {
  name: "Daniel",
  age: 26,
};
user.location; // returns undefined
```

Ultimately, JavaScript will consider this "valid" JavaScript and won’t immediately throw an error. Who knows when the "undefined" will be used in your code, potentially causing bugs.

In TypeScript, the following code produces an error about location not being defined on build or in the editor:

```ts
const user = {
  name: "Daniel",
  age: 26,
};
 
user.location; // Property 'location' does not exist on type '{ name: string; age: number; }'.
```

For example, if you have a function that expects a specific type of object, TypeScript will ensure that you are passing the correct shape of data, preventing bugs before they happen. 

For details on TypeScript's type system and how to use it effectively, check out the official documentation on [Everyday Types](https://www.typescriptlang.org/docs/handbook/2/everyday-types.html).

## Type Checking

While Vite compiles TypeScript quickly, it does not run full type checking during dev mode. In UserFrosting, type checking is handled by `vue-tsc` through the bundled npm script:

```bash
npm run typecheck
```

Under the hood, this runs:

```bash
vue-tsc --noEmit
```

UserFrosting also wires type checking into build and editor workflows:
- `npm run vite:build` runs `vue-tsc && vite build`
- VS Code task: `Frontend - Type Check`
- VS Code task: `Frontend - Eslint`
- VS Code task: `Frontend - Vite Build`

You can run these from the VS Code Command Palette via `Tasks: Run Task`.

For team workflows, run type checking and linting in CI before deployment.

> [!TIP]
> In VS Code, install the ESLint extension (`dbaeumer.vscode-eslint`) to see linting errors and fixes directly in the editor while you type.

## TypeScript Configuration

TypeScript configuration is defined in `tsconfig.json`. UserFrosting ships with a setup that works with Vite, Vue SFCs, and modern ESM.

Key options explained:
- `strict`: enables safer checks across your app
- `moduleResolution: "Bundler"`: aligns TypeScript with Vite's module resolution
- `noEmit`: useful when Vite handles bundling output
- `types: ["vite/client"]`: enables typing for Vite globals like `import.meta.env`
- `include`: ensures `.vue` and `.ts` files are type-checked together
