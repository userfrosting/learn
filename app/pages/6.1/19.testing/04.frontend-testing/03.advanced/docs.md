---
title: Advanced Testing
description: Test Vue composables, Pinia stores, async code, and master lifecycle hooks for complex testing scenarios.
---

This page covers advanced testing techniques for composables, Pinia stores, asynchronous operations, and test lifecycle management.

## Testing Composables

Test Vue composables in isolation without mounting a component:

```ts
import { describe, test, expect } from 'vitest'
import { useCounter } from '../composables/useCounter'

describe('useCounter', () => {
    test('initializes with zero', () => {
        const { count, increment, decrement } = useCounter()

        expect(count.value).toBe(0)
    })

    test('increments count', () => {
        const { count, increment } = useCounter()

        increment()
        expect(count.value).toBe(1)

        increment()
        expect(count.value).toBe(2)
    })

    test('accepts initial value', () => {
        const { count } = useCounter(10)

        expect(count.value).toBe(10)
    })
})
```

### Testing Composables with Side Effects

```ts
import { describe, test, expect, vi } from 'vitest'
import { useApiCall } from '../composables/useApiCall'

vi.mock('../api/client', () => ({
    fetchData: vi.fn().mockResolvedValue({ data: 'mocked' })
}))

describe('useApiCall', () => {
    test('fetches data and updates state', async () => {
        const { data, loading, error, fetch } = useApiCall()

        expect(loading.value).toBe(false)
        expect(data.value).toBeNull()

        const promise = fetch('/api/users')
        expect(loading.value).toBe(true)

        await promise

        expect(loading.value).toBe(false)
        expect(data.value).toEqual({ data: 'mocked' })
        expect(error.value).toBeNull()
    })

    test('handles errors correctly', async () => {
        const { data, loading, error, fetch } = useApiCall()

        // Mock error
        vi.mocked(fetchData).mockRejectedValueOnce(new Error('API Error'))

        await fetch('/api/users')

        expect(loading.value).toBe(false)
        expect(data.value).toBeNull()
        expect(error.value?.message).toBe('API Error')
    })
})
```

## Testing Pinia Stores

Test stores with Pinia's test helpers:

```ts
import { describe, test, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useUserStore } from '../stores/userStore'

describe('useUserStore', () => {
    beforeEach(() => {
        // Create a fresh Pinia instance for each test
        setActivePinia(createPinia())
    })

    test('initializes with default state', () => {
        const store = useUserStore()

        expect(store.users).toEqual([])
        expect(store.loading).toBe(false)
        expect(store.currentUser).toBeNull()
    })

    test('adds user to store', () => {
        const store = useUserStore()
        const user = { id: 1, name: 'Alice' }

        store.addUser(user)

        expect(store.users).toHaveLength(1)
        expect(store.users[0]).toEqual(user)
    })

    test('removes user by id', () => {
        const store = useUserStore()
        store.users = [
            { id: 1, name: 'Alice' },
            { id: 2, name: 'Bob' }
        ]

        store.removeUser(1)

        expect(store.users).toHaveLength(1)
        expect(store.users[0].id).toBe(2)
    })
})
```

### Testing Store Getters

```ts
test('computes active users correctly', () => {
    const store = useUserStore()
    store.users = [
        { id: 1, name: 'Alice', active: true },
        { id: 2, name: 'Bob', active: false },
        { id: 3, name: 'Charlie', active: true }
    ]

    expect(store.activeUsers).toHaveLength(2)
    expect(store.activeUsers[0].name).toBe('Alice')
    expect(store.activeUsers[1].name).toBe('Charlie')
})
```

### Testing Store Actions

```ts
import { describe, test, expect, beforeEach, vi } from 'vitest'
import axios from 'axios'

vi.mock('axios')

describe('userStore actions', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    test('fetchUsers updates state on success', async () => {
        const mockUsers = [{ id: 1, name: 'Alice' }]
        vi.mocked(axios.get).mockResolvedValue({ data: mockUsers })

        const store = useUserStore()
        await store.fetchUsers()

        expect(store.loading).toBe(false)
        expect(store.users).toEqual(mockUsers)
        expect(store.error).toBeNull()
    })

    test('fetchUsers handles errors', async () => {
        vi.mocked(axios.get).mockRejectedValue(new Error('Network error'))

        const store = useUserStore()
        await store.fetchUsers()

        expect(store.loading).toBe(false)
        expect(store.users).toEqual([])
        expect(store.error).toBe('Network error')
    })
})
```

## Testing Async Code

Use `async/await` for asynchronous operations:

```ts
import { describe, test, expect, vi } from 'vitest'
import axios from 'axios'

vi.mock('axios')

describe('Async operations', () => {
    test('fetches data from API', async () => {
        const mockData = { id: 1, name: 'Test' }
        vi.mocked(axios.get).mockResolvedValue({ data: mockData })

        const response = await axios.get('/api/data')

        expect(response.data).toEqual(mockData)
        expect(axios.get).toHaveBeenCalledWith('/api/data')
    })

    test('handles API errors', async () => {
        vi.mocked(axios.get).mockRejectedValue(new Error('Network error'))

        await expect(axios.get('/api/data')).rejects.toThrow('Network error')
    })
})
```

### Testing Components with Async Data

```ts
test('displays data after loading', async () => {
    vi.mocked(axios.get).mockResolvedValue({
        data: { users: [{ id: 1, name: 'Alice' }] }
    })

    const wrapper = mount(UserList)

    // Initially shows loading state
    expect(wrapper.find('[data-test="spinner"]').exists()).toBe(true)

    // Wait for async operations to complete
    await wrapper.vm.$nextTick()
    await flushPromises() // Helper to flush all pending promises

    // Now shows data
    expect(wrapper.find('[data-test="spinner"]').exists()).toBe(false)
    expect(wrapper.text()).toContain('Alice')
})
```

### Flush Promises Helper

Create a helper to wait for all pending promises:

```ts
// app/assets/tests/helpers.ts
export const flushPromises = () => new Promise(resolve => setImmediate(resolve))
```

```ts
import { flushPromises } from './helpers'

test('handles async updates', async () => {
    const wrapper = mount(AsyncComponent)
    
    await flushPromises()
    
    expect(wrapper.text()).toContain('Loaded')
})
```

## Test Lifecycle Hooks

Vitest provides hooks for setup and teardown:

```ts
import { describe, test, beforeEach, afterEach, beforeAll, afterAll, vi } from 'vitest'

describe('User Management', () => {
    // Runs once before all tests
    beforeAll(() => {
        console.log('Setting up test suite')
    })

    // Runs once after all tests
    afterAll(() => {
        console.log('Tearing down test suite')
    })

    // Runs before each test
    beforeEach(() => {
        setActivePinia(createPinia())
        vi.clearAllMocks()
    })

    // Runs after each test
    afterEach(() => {
        vi.resetAllMocks()
    })

    test('creates user', () => {
        // Test code
    })

    test('updates user', () => {
        // Test code
    })
})
```

### Nested Describe Blocks

Organize related tests with nested `describe` blocks:

```ts
describe('UserStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    describe('state management', () => {
        test('initializes correctly', () => {
            // Test initialization
        })

        test('updates state', () => {
            // Test state updates
        })
    })

    describe('API integration', () => {
        beforeEach(() => {
            // Additional setup for API tests
            vi.mock('axios')
        })

        test('fetches data', async () => {
            // Test API fetch
        })

        test('handles errors', async () => {
            // Test error handling
        })
    })
})
```

## Testing with watchers

Test Vue's watch and watchEffect:

```ts
import { describe, test, expect, vi } from 'vitest'
import { ref, watch } from 'vue'

test('watcher triggers on value change', async () => {
    const count = ref(0)
    const callback = vi.fn()

    watch(count, callback)

    count.value = 1
    await nextTick() // Wait for watchers to trigger

    expect(callback).toHaveBeenCalledWith(1, 0, expect.anything())
})

test('component watcher updates correctly', async () => {
    const wrapper = mount(SearchBox)

    await wrapper.find('input').setValue('test')
    await wrapper.vm.$nextTick()

    // Verify watcher side effect
    expect(wrapper.vm.debouncedSearch).toHaveBeenCalled()
})
```

## Testing Error Boundaries

Test error handling in components:

```ts
test('catches and displays errors', async () => {
    // Mock component that throws error
    const ErrorComponent = {
        setup() {
            throw new Error('Component error')
        },
        template: '<div>Should not render</div>'
    }

    const wrapper = mount(ErrorBoundary, {
        slots: {
            default: ErrorComponent
        }
    })

    expect(wrapper.find('[data-test="error-message"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Something went wrong')
})
```

## Testing Teleport

Test components using `<Teleport>`:

```ts
test('renders modal in portal', () => {
    // Create target element
    const target = document.createElement('div')
    target.id = 'modal-root'
    document.body.appendChild(target)

    const wrapper = mount(ModalComponent, {
        props: { isOpen: true },
        attachTo: document.body
    })

    expect(target.innerHTML).toContain('Modal content')

    wrapper.unmount()
    document.body.removeChild(target)
})
```

## Testing Transitions

Test components with Vue transitions:

```ts
test('applies transition classes', async () => {
    const wrapper = mount(TransitionComponent)

    // Trigger transition
    await wrapper.setProps({ show: true })

    // Check for enter classes
    expect(wrapper.find('.fade-enter-active').exists()).toBe(true)

    // Wait for transition to complete
    await new Promise(resolve => setTimeout(resolve, 300))

    expect(wrapper.find('.fade-enter-active').exists()).toBe(false)
})
```

## Performance Testing

Test component performance:

```ts
test('renders large list efficiently', () => {
    const items = Array.from({ length: 10000 }, (_, i) => ({
        id: i,
        name: `Item ${i}`
    }))

    const start = performance.now()
    const wrapper = mount(VirtualList, {
        props: { items }
    })
    const duration = performance.now() - start

    expect(duration).toBeLessThan(100) // Should render in <100ms
    expect(wrapper.findAll('[data-test="list-item"]')).toHaveLength(expect.any(Number))
})
```

## Next Steps

You've mastered advanced testing techniques! Now learn best practices:

- **[Best Practices](/testing/frontend-testing/best-practices)**: Write maintainable, effective tests with real-world examples
