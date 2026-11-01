---
title: Testing Vue Components
description: Master testing Vue 3 components with Vue Test Utils, including props, events, slots, and DOM interactions.
---

Vue Test Utils provides powerful utilities for mounting and testing Vue components in isolation. This page covers everything you need to test your Vue components effectively.

## Basic Component Testing

Use the `mount` function from Vue Test Utils to create a wrapper around your component:

```ts
import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MyComponent from '../components/MyComponent.vue'

describe('MyComponent.vue', () => {
    test('renders props correctly', () => {
        const wrapper = mount(MyComponent, {
            props: {
                title: 'Hello World'
            }
        })

        expect(wrapper.text()).toContain('Hello World')
    })
})
```

## Testing Props

Verify that your component correctly handles different prop values:

```ts
test('displays user information from props', () => {
    const wrapper = mount(UserCard, {
        props: {
            name: 'John Doe',
            email: 'john@example.com',
            role: 'Admin'
        }
    })

    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('john@example.com')
    expect(wrapper.text()).toContain('Admin')
})

test('shows placeholder when no props provided', () => {
    const wrapper = mount(UserCard)

    expect(wrapper.text()).toContain('No user selected')
})
```

## Testing Events

Test that your component emits the correct events:

```ts
test('emits submit event on button click', async () => {
    const wrapper = mount(MyForm)
    
    await wrapper.find('button[type="submit"]').trigger('click')

    expect(wrapper.emitted('submit')).toBeTruthy()
    expect(wrapper.emitted('submit')).toHaveLength(1)
})

test('emits event with correct payload', async () => {
    const wrapper = mount(SearchBox)
    const input = wrapper.find('input')
    
    await input.setValue('test query')
    await input.trigger('keyup.enter')

    expect(wrapper.emitted('search')).toBeTruthy()
    expect(wrapper.emitted('search')[0]).toEqual(['test query'])
})
```

## Testing Slots

Verify that your component renders slot content correctly:

```ts
test('renders default slot content', () => {
    const wrapper = mount(Card, {
        slots: {
            default: '<p>This is slot content</p>'
        }
    })

    expect(wrapper.html()).toContain('<p>This is slot content</p>')
})

test('renders named slots', () => {
    const wrapper = mount(Modal, {
        slots: {
            header: '<h1>Modal Title</h1>',
            default: '<p>Modal body content</p>',
            footer: '<button>Close</button>'
        }
    })

    expect(wrapper.find('h1').text()).toBe('Modal Title')
    expect(wrapper.find('p').text()).toBe('Modal body content')
    expect(wrapper.find('button').text()).toBe('Close')
})

test('renders scoped slot with data', () => {
    const wrapper = mount(List, {
        slots: {
            default: `
                <template #default="{ item }">
                    <span>{{ item.name }}</span>
                </template>
            `
        }
    })

    expect(wrapper.html()).toContain('span')
})
```

## Testing User Interactions

Simulate user interactions with the DOM:

```ts
test('toggles visibility on button click', async () => {
    const wrapper = mount(Collapsible)

    expect(wrapper.find('.content').isVisible()).toBe(false)

    await wrapper.find('button.toggle').trigger('click')
    expect(wrapper.find('.content').isVisible()).toBe(true)

    await wrapper.find('button.toggle').trigger('click')
    expect(wrapper.find('.content').isVisible()).toBe(false)
})

test('updates input value', async () => {
    const wrapper = mount(TextField)
    const input = wrapper.find('input')

    await input.setValue('New value')

    expect(input.element.value).toBe('New value')
})

test('submits form with input values', async () => {
    const wrapper = mount(ContactForm)

    await wrapper.find('input[name="name"]').setValue('John Doe')
    await wrapper.find('input[name="email"]').setValue('john@example.com')
    await wrapper.find('textarea[name="message"]').setValue('Hello!')
    await wrapper.find('form').trigger('submit')

    expect(wrapper.emitted('submit')[0][0]).toEqual({
        name: 'John Doe',
        email: 'john@example.com',
        message: 'Hello!'
    })
})
```

## Using Data-test Attributes

Use `data-test` attributes for stable, reliable selectors:

```vue
<template>
    <div data-test="user-profile">
        <h1 data-test="user-name">{{ name }}</h1>
        <p data-test="user-email">{{ email }}</p>
        <button data-test="edit-button" @click="onEdit">Edit</button>
    </div>
</template>
```

```ts
test('finds elements by data-test attributes', () => {
    const wrapper = mount(UserProfile, {
        props: {
            name: 'Jane Smith',
            email: 'jane@example.com'
        }
    })

    expect(wrapper.get('[data-test="user-name"]').text()).toBe('Jane Smith')
    expect(wrapper.get('[data-test="user-email"]').text()).toBe('jane@example.com')
    expect(wrapper.find('[data-test="edit-button"]').exists()).toBe(true)
})
```

> [!TIP]
> Use `wrapper.get('[selector]')` when you expect the element to exist (throws error if not found). Use `wrapper.find('[selector]')` when the element might not exist (returns a wrapper that can be checked with `.exists()`).

## Testing Conditional Rendering

Test `v-if`, `v-show`, and other conditional directives:

```ts
test('shows loading spinner when loading', () => {
    const wrapper = mount(DataTable, {
        props: { loading: true }
    })

    expect(wrapper.find('[data-test="spinner"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="table"]').exists()).toBe(false)
})

test('shows table when not loading', () => {
    const wrapper = mount(DataTable, {
        props: { loading: false }
    })

    expect(wrapper.find('[data-test="spinner"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="table"]').exists()).toBe(true)
})

test('shows error message when error prop is set', () => {
    const wrapper = mount(AlertBox, {
        props: {
            error: 'Something went wrong'
        }
    })

    expect(wrapper.find('[data-test="error"]').text()).toBe('Something went wrong')
})
```

## Testing Lists and Loops

Test components that render lists with `v-for`:

```ts
test('renders all items in list', () => {
    const items = [
        { id: 1, name: 'Item 1' },
        { id: 2, name: 'Item 2' },
        { id: 3, name: 'Item 3' }
    ]

    const wrapper = mount(ItemList, {
        props: { items }
    })

    const listItems = wrapper.findAll('[data-test="list-item"]')
    expect(listItems).toHaveLength(3)
    expect(listItems[0].text()).toContain('Item 1')
    expect(listItems[1].text()).toContain('Item 2')
    expect(listItems[2].text()).toContain('Item 3')
})

test('shows empty state when no items', () => {
    const wrapper = mount(ItemList, {
        props: { items: [] }
    })

    expect(wrapper.find('[data-test="empty-state"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('No items found')
})
```

## Testing Computed Properties

Verify that computed properties work correctly:

```ts
test('displays formatted price', () => {
    const wrapper = mount(ProductCard, {
        props: {
            price: 19.99,
            currency: 'USD'
        }
    })

    expect(wrapper.find('[data-test="price"]').text()).toBe('$19.99')
})

test('shows discounted price when on sale', () => {
    const wrapper = mount(ProductCard, {
        props: {
            price: 100,
            discount: 20
        }
    })

    expect(wrapper.find('[data-test="original-price"]').text()).toContain('$100')
    expect(wrapper.find('[data-test="sale-price"]').text()).toContain('$80')
})
```

## Testing Reactivity

Test that the component updates when data changes:

```ts
test('updates display when prop changes', async () => {
    const wrapper = mount(Counter, {
        props: { count: 0 }
    })

    expect(wrapper.text()).toContain('Count: 0')

    await wrapper.setProps({ count: 5 })
    expect(wrapper.text()).toContain('Count: 5')
})

test('rerenders when reactive data updates', async () => {
    const wrapper = mount(ToggleButton)

    expect(wrapper.find('button').text()).toBe('Off')

    await wrapper.find('button').trigger('click')
    expect(wrapper.find('button').text()).toBe('On')
})
```

## Wrapper Methods Reference

### Finding Elements

- `wrapper.find(selector)` — Find first matching element (returns wrapper)
- `wrapper.findAll(selector)` — Find all matching elements (returns array of wrappers)
- `wrapper.get(selector)` — Find first matching element (throws if not found)
- `wrapper.findComponent(Component)` — Find child component

### Checking Elements

- `wrapper.exists()` — Returns true if element exists
- `wrapper.isVisible()` — Returns true if element is visible (not hidden via CSS)
- `wrapper.text()` — Get text content
- `wrapper.html()` — Get HTML content
- `wrapper.classes()` — Get array of CSS classes
- `wrapper.attributes()` — Get object of attributes

### Interacting with Elements

- `wrapper.trigger(event)` — Trigger an event
- `wrapper.setValue(value)` — Set input value
- `wrapper.setChecked(checked)` — Set checkbox/radio state
- `wrapper.setProps(props)` — Update component props

### Checking Events

- `wrapper.emitted()` — Get all emitted events
- `wrapper.emitted(eventName)` — Get array of times event was emitted
- `wrapper.emitted(eventName)[0]` — Get payload of first emit

## Next Steps

Now that you know how to test Vue components, learn about:

- **[Mocking & Stubbing](/testing/frontend-testing/mocking)**: Isolate your components by mocking dependencies
- **[Advanced Testing](/testing/frontend-testing/advanced)**: Test composables, stores, and handle async code
- **[Best Practices](/testing/frontend-testing/best-practices)**: Write maintainable, effective tests
