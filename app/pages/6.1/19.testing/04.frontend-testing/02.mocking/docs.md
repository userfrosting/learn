---
title: Mocking & Stubbing
description: Learn how to isolate components by mocking modules, Pinia stores, Vue Router, and global properties in Vitest.
---

Mocking allows you to isolate the component you're testing by replacing its dependencies with controlled substitutes. This makes tests faster, more reliable, and easier to write.

## Mocking Modules

Use `vi.mock()` to replace entire modules:

```ts
import { describe, test, expect, vi } from 'vitest'
import { useMyComposable } from '../composables/useMyComposable'

// Mock the API module
vi.mock('../api/myApi', () => ({
    fetchData: vi.fn().mockResolvedValue({ data: 'mocked' }),
    postData: vi.fn().mockResolvedValue({ success: true })
}))

describe('useMyComposable', () => {
    test('calls API and returns data', async () => {
        const { data } = await useMyComposable()
        expect(data).toEqual({ data: 'mocked' })
    })
})
```

### Mocking with Custom Implementation

```ts
vi.mock('../services/authService', () => ({
    login: vi.fn((credentials) => {
        if (credentials.email === 'test@example.com') {
            return Promise.resolve({ token: 'fake-token' })
        }
        return Promise.reject(new Error('Invalid credentials'))
    }),
    logout: vi.fn().mockResolvedValue(undefined)
}))
```

## Mocking Pinia Stores

Mock stores to isolate component tests from application state:

```ts
import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { useAuthStore } from '@userfrosting/sprinkle-core/stores'
import MyComponent from '../components/MyComponent.vue'

// Mock the store module
vi.mock('@userfrosting/sprinkle-core/stores')

describe('MyComponent.vue', () => {
    beforeEach(() => {
        vi.mocked(useAuthStore).mockReturnValue({
            user: { name: 'Test User', email: 'test@example.com' },
            isAuthenticated: true,
            login: vi.fn(),
            logout: vi.fn()
        } as any)
    })

    test('displays user name', () => {
        const wrapper = mount(MyComponent)
        expect(wrapper.text()).toContain('Test User')
    })

    test('calls logout when button clicked', async () => {
        const wrapper = mount(MyComponent)
        const logoutMock = vi.mocked(useAuthStore).mock.results[0].value.logout

        await wrapper.find('[data-test="logout-button"]').trigger('click')

        expect(logoutMock).toHaveBeenCalled()
    })
})
```

### Testing Different Store States

```ts
test('shows login form when not authenticated', () => {
    vi.mocked(useAuthStore).mockReturnValue({
        user: null,
        isAuthenticated: false,
        login: vi.fn(),
        logout: vi.fn()
    } as any)

    const wrapper = mount(Navigation)
    expect(wrapper.find('[data-test="login-form"]').exists()).toBe(true)
})

test('shows user menu when authenticated', () => {
    vi.mocked(useAuthStore).mockReturnValue({
        user: { name: 'Jane' },
        isAuthenticated: true,
        login: vi.fn(),
        logout: vi.fn()
    } as any)

    const wrapper = mount(Navigation)
    expect(wrapper.find('[data-test="user-menu"]').exists()).toBe(true)
})
```

## Mocking Vue Router

### Creating a Real Router

For simple navigation tests, create a real router:

```ts
import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import MyComponent from '../components/MyComponent.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
        { path: '/about', name: 'about', component: { template: '<div>About</div>' } }
    ]
})

describe('MyComponent.vue', () => {
    test('navigates to about page on button click', async () => {
        const wrapper = mount(MyComponent, {
            global: {
                plugins: [router]
            }
        })

        await wrapper.find('[data-test="about-link"]').trigger('click')
        
        expect(router.currentRoute.value.name).toBe('about')
    })
})
```

### Mocking the Router Entirely

For more control, mock Vue Router:

```ts
const mockPush = vi.fn()
const mockReplace = vi.fn()

vi.mock('vue-router', () => ({
    useRouter: () => ({
        push: mockPush,
        replace: mockReplace,
        back: vi.fn(),
        forward: vi.fn()
    }),
    useRoute: () => ({
        path: '/current-path',
        params: { id: '123' },
        query: { search: 'test' }
    })
}))

describe('NavigationComponent', () => {
    test('navigates to user profile', async () => {
        const wrapper = mount(UserCard, {
            props: { userId: 42 }
        })

        await wrapper.find('[data-test="view-profile"]').trigger('click')

        expect(mockPush).toHaveBeenCalledWith({ name: 'user-profile', params: { id: 42 } })
    })
})
```

## Mocking Global Properties

If your Vue app uses global properties (like `$t` for translations), mock them in a setup file:

```ts
// app/assets/tests/setup.ts
import { config } from '@vue/test-utils'

config.global.mocks = {
    $t: (key: string) => key, // Return translation key as-is
    $tdate: (date: string) => date,
    $can: (permission: string) => true // Mock permission check
}
```

Reference this file in your [vite.config.ts](vite.config.ts):

```ts
export default defineConfig({
    test: {
        setupFiles: ['app/assets/tests/setup.ts']
    }
})
```

### Per-test Global Mocks

Override global mocks for specific tests:

```ts
test('shows permission-restricted content', () => {
    const wrapper = mount(AdminPanel, {
        global: {
            mocks: {
                $can: (permission: string) => permission === 'admin.access'
            }
        }
    })

    expect(wrapper.find('[data-test="admin-content"]').exists()).toBe(true)
})

test('hides content without permission', () => {
    const wrapper = mount(AdminPanel, {
        global: {
            mocks: {
                $can: () => false
            }
        }
    })

    expect(wrapper.find('[data-test="admin-content"]').exists()).toBe(false)
})
```

## Stubbing Child Components

Stub child components to isolate the component you're testing:

```ts
import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ParentComponent from '../components/ParentComponent.vue'

describe('ParentComponent.vue', () => {
    test('renders with stubbed children', () => {
        const wrapper = mount(ParentComponent, {
            global: {
                stubs: ['ChildComponent', 'AnotherChild']
            }
        })

        // Child components are replaced with simple stubs
        expect(wrapper.html()).toContain('<child-component-stub')
    })

    test('passes props to child component', () => {
        const wrapper = mount(ParentComponent, {
            global: {
                stubs: {
                    ChildComponent: {
                        template: '<div>{{ title }}</div>',
                        props: ['title']
                    }
                }
            }
        })

        expect(wrapper.html()).toContain('Expected Title')
    })
})
```

### Global Component Stubs

Register global stubs that apply to all tests:

```ts
// app/assets/tests/setup.ts
import { config } from '@vue/test-utils'

config.global.stubs = {
    FontAwesomeIcon: true, // Auto-stub
    RouterLink: {
        template: '<a><slot /></a>' // Custom stub
    }
}
```

## Mocking Axios/HTTP Requests

Mock axios or other HTTP libraries:

```ts
import { describe, test, expect, vi, beforeEach } from 'vitest'
import axios from 'axios'

vi.mock('axios')

describe('User API', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    test('fetches users successfully', async () => {
        const mockUsers = [
            { id: 1, name: 'Alice' },
            { id: 2, name: 'Bob' }
        ]

        vi.mocked(axios.get).mockResolvedValue({ data: mockUsers })

        const response = await axios.get('/api/users')

        expect(axios.get).toHaveBeenCalledWith('/api/users')
        expect(response.data).toEqual(mockUsers)
    })

    test('handles API errors', async () => {
        vi.mocked(axios.get).mockRejectedValue(new Error('Network error'))

        await expect(axios.get('/api/users')).rejects.toThrow('Network error')
    })
})
```

### Mocking Different Responses

```ts
test('handles different status codes', async () => {
    // Success
    vi.mocked(axios.post).mockResolvedValue({
        status: 200,
        data: { message: 'Success' }
    })

    // Error
    vi.mocked(axios.post).mockRejectedValue({
        response: {
            status: 422,
            data: { errors: { email: ['Email already exists'] } }
        }
    })
})
```

## Spy Functions

Use spies to track function calls without replacing implementation:

```ts
test('calls callback on success', async () => {
    const onSuccess = vi.fn()
    const wrapper = mount(FileUploader, {
        props: { onSuccess }
    })

    // Simulate successful upload
    await wrapper.vm.handleUpload()

    expect(onSuccess).toHaveBeenCalledTimes(1)
    expect(onSuccess).toHaveBeenCalledWith({ filename: 'test.pdf' })
})

test('does not call onSuccess on error', async () => {
    const onSuccess = vi.fn()
    const wrapper = mount(FileUploader, {
        props: { onSuccess }
    })

    // Simulate error
    await wrapper.vm.handleError()

    expect(onSuccess).not.toHaveBeenCalled()
})
```

## Mock Timers

Control time in your tests:

```ts
import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'

describe('Timer Component', () => {
    beforeEach(() => {
        vi.useFakeTimers()
    })

    afterEach(() => {
        vi.restoreAllMocks()
    })

    test('shows message after delay', async () => {
        const wrapper = mount(DelayedMessage, {
            props: { delay: 3000 }
        })

        expect(wrapper.find('[data-test="message"]').exists()).toBe(false)

        // Fast-forward time
        vi.advanceTimersByTime(3000)
        await wrapper.vm.$nextTick()

        expect(wrapper.find('[data-test="message"]').exists()).toBe(true)
    })

    test('clears timeout on unmount', () => {
        const clearTimeoutSpy = vi.spyOn(global, 'clearTimeout')
        const wrapper = mount(DelayedMessage)

        wrapper.unmount()

        expect(clearTimeoutSpy).toHaveBeenCalled()
    })
})
```

## Cleaning Up Mocks

Always reset mocks between tests:

```ts
import { describe, test, afterEach, vi } from 'vitest'

describe('My Tests', () => {
    afterEach(() => {
        vi.clearAllMocks()   // Clears call history
        vi.resetAllMocks()   // Resets implementation
        vi.restoreAllMocks() // Restores original implementation
    })

    // Your tests...
})
```

### Mock Cleanup Methods

- `vi.clearAllMocks()` — Clears call history, keeps implementation
- `vi.resetAllMocks()` — Clears history and resets to initial implementation
- `vi.restoreAllMocks()` — Restores original implementation (for spies)

## Next Steps

Now that you can mock dependencies, explore:

- **[Advanced Testing](/testing/frontend-testing/advanced)**: Test composables, stores, and async code
- **[Best Practices](/testing/frontend-testing/best-practices)**: Write maintainable tests with real-world examples
