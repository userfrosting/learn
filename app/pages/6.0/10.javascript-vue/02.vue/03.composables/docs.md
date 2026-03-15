---
title: Composables
description: Learn how composables help you share reactive logic across Vue components.
---

[Composables](https://vuejs.org/guide/reusability/composables.html) are reusable functions that encapsulate reactive logic. They let you share stateful behavior between components without copy-pasting code.

Think of composables as utility functions with lifecycle hooks and reactive state built in.

## Simple Example: Mouse Position

```typescript
// composables/useMouse.ts
import { ref, onMounted, onUnmounted } from 'vue'

export function useMouse() {
  const x = ref(0)
  const y = ref(0)

  function updatePosition(event: MouseEvent) {
    x.value = event.pageX
    y.value = event.pageY
  }

  onMounted(() => {
    window.addEventListener('mousemove', updatePosition)
  })

  onUnmounted(() => {
    window.removeEventListener('mousemove', updatePosition)
  })

  return { x, y }
}
```

Using the composable:

```vue
<script setup lang="ts">
import { useMouse } from '@/composables/useMouse'

const { x, y } = useMouse()
</script>

<template>
  <p>Mouse position: {{ x }}, {{ y }}</p>
</template>
```

> [!NOTE]
> To see this in action, check the [Vue composables guide](https://vuejs.org/guide/reusability/composables.html#mouse-tracker-example) and the [Vue Playground](https://play.vuejs.org/#eNqNUttq4zAQ/ZVBL3HBuAu7T6Ut7C55aOmNXqAFvRh7kqq1R0IXxyH43zuSEyeFUvpia2bOHJ0zo434a0zRBRQn4tRVVhkPDn0w55JUa7T1sIHg8FrzBwZYWN3CrDhuY1y8uZkkSZUmF3F9DmvGnE0N2ZGk0+ORlgk58NiapvTIEcBIarRTXmkC5aD0J7BhIhiGPB6YbogUU5vIxe5qVjwptLjIQRMTksc6Hp+IcTGYRLPJpBb71LQIVKVr92JhE1WNbnq2wazZL7awy60PcjG7pzA1i8uwQ/JbFoC+6MomIPekfGHKJT6PpfUXpZdYYrPxNznJWNTZOawU1XpVlHU9j/gr5TwS2myWZtHqDmf5VsRR0nvg/zOFTegfsUQey0/B0rRaSQMvwDsex0IteQeaeAvJrxSVbo1q0N6aOBMnBa9ytCtF2TR6dZly3gbMd/nqFav3L/Jvro85Ke4sOrQdSjHVfGmX6Mfy/OEGez5PxVbXoWH0N8V7dLoJUeMI+xeoZtkHuKT2Ir0tRctHN+95UG5nKgpNu0p4Kfhd/f/G+l7u7+LPdseDGD4A5q4sqQ==).

## Why Destructure?

You may have noticed that composables return an object and callers always destructure it:

```typescript
const { x, y } = useMouse()
```

Composables return **objects of `ref`s**, not plain values. This matters: you can destructure `{ x, y }` out of the object safely because each property is already a `ref` — the reactivity is carried inside the ref itself, not in the object wrapper. Pulling `x` and `y` out doesn't break anything.

Compare that to `reactive()`, which wraps an entire object. Destructuring a `reactive()` object *does* break reactivity because you end up with plain, disconnected values:

```typescript
const mouse = reactive({ x: 0, y: 0 })
const { x } = mouse  // ❌ x is now a plain number — no longer reactive
```

```typescript
const mouse = { x: ref(0), y: ref(0) }
const { x } = mouse  // ✅ x is still a ref — still reactive
```

This is why composables consistently return `{ x, y }` (an object of refs) rather than a single `reactive()` object — it's the pattern that allows safe, intuitive destructuring in the caller.

You might also wonder why you don't just do `useMouse().x`. The problem is that every call to `useMouse()` creates a **new, independent instance** of the composable — with its own state and its own event listeners. Calling it twice registers two `mousemove` listeners:

```typescript
// ❌ Two separate instances — two event listeners, two independent counters
const x = useMouse().x
const y = useMouse().y
```

Destructuring from a single call gives you both values from the same instance:

```typescript
// ✅ One instance — one event listener, x and y are always in sync
const { x, y } = useMouse()
```
