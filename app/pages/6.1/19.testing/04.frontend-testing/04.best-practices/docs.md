---
title: Best Practices
description: Follow proven patterns for writing maintainable, effective tests with real-world examples.
---

Writing good tests is an art. This page covers best practices that will help you write tests that are maintainable, reliable, and effective.

## 1. Use Descriptive Test Names

Test names should clearly describe what is being tested and what the expected behavior is.

```ts
// ❌ Bad - vague and unhelpful
test('test 1', () => { ... })
test('works', () => { ... })
test('button', () => { ... })

// ✅ Good - clear and descriptive
test('displays error message when email is invalid', () => { ... })
test('submits form when all required fields are filled', () => { ... })
test('disables submit button while request is pending', () => { ... })
```

### Name Structure

Use this pattern: **"[action/state] when [condition]"**

```ts
test('shows loading spinner when data is fetching', () => { ... })
test('enables save button when form is valid', () => { ... })
test('redirects to login when user is not authenticated', () => { ... })
```

## 2. Follow the AAA Pattern

Structure your tests with **Arrange, Act, Assert**:

```ts
test('adds item to cart', () => {
    // Arrange - Set up test data and state
    const cart = createCart()
    const item = { id: 1, name: 'Widget', price: 10 }

    // Act - Perform the action being tested
    cart.add(item)

    // Assert - Verify the expected outcome
    expect(cart.items).toContain(item)
    expect(cart.total).toBe(10)
})
```

This pattern makes tests easier to read and understand.

## 3. Test Behavior, Not Implementation

Focus on **what** the code does, not **how** it does it:

```ts
// ❌ Bad - tests internal implementation
test('calls internal _calculateTotal method', () => {
    const cart = createCart()
    const spy = vi.spyOn(cart, '_calculateTotal')
    
    cart.checkout()
    
    expect(spy).toHaveBeenCalled()
})

// ✅ Good - tests observable behavior
test('checkout calculates correct total including tax', () => {
    const cart = createCart()
    cart.add({ price: 100 })
    cart.setTaxRate(0.10)
    
    const total = cart.checkout()
    
    expect(total).toBe(110)
})
```

**Why?** Implementation can change, but behavior should remain stable. Testing behavior makes tests more resilient to refactoring.

## 4. Keep Tests Independent

Each test should run independently without relying on other tests:

```ts
// ❌ Bad - tests depend on execution order
let user: User

test('creates user', () => {
    user = createUser({ name: 'Alice' })
    expect(user).toBeDefined()
})

test('updates user', () => {
    user.name = 'Bob' // Depends on previous test!
    expect(user.name).toBe('Bob')
})

// ✅ Good - each test is independent
test('creates user with given name', () => {
    const user = createUser({ name: 'Alice' })
    expect(user.name).toBe('Alice')
})

test('updates user name', () => {
    const user = createUser({ name: 'Alice' })
    user.name = 'Bob'
    expect(user.name).toBe('Bob')
})
```

## 5. Use Data-test Attributes

Avoid coupling tests to implementation details like CSS classes:

```vue
<!-- ❌ Bad - brittle selector tied to styling -->
<button class="btn btn-primary bg-blue-500">Submit</button>

<!-- ✅ Good - stable test selector -->
<button class="btn btn-primary bg-blue-500" data-test="submit-button">Submit</button>
```

```ts
// Test with stable selector
test('submits form on button click', async () => {
    const wrapper = mount(MyForm)
    
    await wrapper.find('[data-test="submit-button"]').trigger('click')
    
    expect(wrapper.emitted('submit')).toBeTruthy()
})
```

**Why?** CSS classes can change when you update styling. Data-test attributes are explicitly for testing and won't change accidentally.

## 6. Don't Test Framework Features

Don't test Vue.js itself — trust that it works:

```ts
// ❌ Bad - testing Vue's reactivity
test('v-model updates data', async () => {
    const wrapper = mount(MyInput)
    await wrapper.find('input').setValue('test')
    expect(wrapper.vm.value).toBe('test')
})

// ✅ Good - testing your component's behavior
test('emits search event when user types query', async () => {
    const wrapper = mount(SearchBox)
    await wrapper.find('input').setValue('test query')
    await wrapper.find('form').trigger('submit')
    
    expect(wrapper.emitted('search')).toBeTruthy()
    expect(wrapper.emitted('search')[0]).toEqual(['test query'])
})
```

## 7. Clean Up After Tests

Always reset mocks and state between tests:

```ts
import { describe, test, afterEach, vi } from 'vitest'

describe('UserService', () => {
    afterEach(() => {
        vi.clearAllMocks()   // Clears call history
        vi.resetAllMocks()   // Resets implementation
        vi.restoreAllMocks() // Restores original implementation
    })

    test('fetches user', async () => {
        // Test code
    })

    test('creates user', async () => {
        // Test code - starts with clean mocks
    })
})
```

## 8. Test Edge Cases

Don't just test the happy path:

```ts
describe('UserInput', () => {
    test('accepts valid email', () => {
        const wrapper = mount(UserInput)
        wrapper.vm.validate('user@example.com')
        expect(wrapper.vm.isValid).toBe(true)
    })

    // Test edge cases
    test('rejects email without @', () => {
        const wrapper = mount(UserInput)
        wrapper.vm.validate('invalid-email')
        expect(wrapper.vm.isValid).toBe(false)
    })

    test('rejects empty email', () => {
        const wrapper = mount(UserInput)
        wrapper.vm.validate('')
        expect(wrapper.vm.isValid).toBe(false)
    })

    test('handles very long email', () => {
        const wrapper = mount(UserInput)
        const longEmail = 'a'.repeat(100) + '@example.com'
        wrapper.vm.validate(longEmail)
        expect(wrapper.vm.isValid).toBe(false)
    })

    test('trims whitespace from email', () => {
        const wrapper = mount(UserInput)
        wrapper.vm.validate('  user@example.com  ')
        expect(wrapper.vm.email).toBe('user@example.com')
    })
})
```

## 9. Use Factories for Test Data

Create reusable factories for consistent test data:

```ts
// tests/factories.ts
export const createUser = (overrides = {}) => ({
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    role: 'user',
    ...overrides
})

export const createProduct = (overrides = {}) => ({
    id: 1,
    name: 'Test Product',
    price: 19.99,
    inStock: true,
    ...overrides
})

// In tests
test('displays user information', () => {
    const user = createUser({ name: 'Alice', role: 'admin' })
    const wrapper = mount(UserCard, { props: { user } })
    
    expect(wrapper.text()).toContain('Alice')
    expect(wrapper.text()).toContain('admin')
})
```

## 10. Keep Tests Focused

Each test should verify one specific behavior:

```ts
// ❌ Bad - testing multiple things
test('user login', async () => {
    const wrapper = mount(LoginForm)
    
    // Tests form validation
    await wrapper.find('input[name="email"]').setValue('')
    expect(wrapper.text()).toContain('Email is required')
    
    // Tests successful login
    await wrapper.find('input[name="email"]').setValue('user@example.com')
    await wrapper.find('input[name="password"]').setValue('password')
    await wrapper.find('form').trigger('submit')
    expect(wrapper.emitted('success')).toBeTruthy()
    
    // Tests error handling
    await wrapper.setProps({ error: 'Invalid credentials' })
    expect(wrapper.text()).toContain('Invalid credentials')
})

// ✅ Good - separate focused tests
test('shows error when email is empty', async () => {
    const wrapper = mount(LoginForm)
    await wrapper.find('input[name="email"]').setValue('')
    await wrapper.find('input[name="email"]').trigger('blur')
    
    expect(wrapper.find('[data-test="email-error"]').text()).toContain('Email is required')
})

test('emits success event on valid submission', async () => {
    const wrapper = mount(LoginForm)
    await wrapper.find('input[name="email"]').setValue('user@example.com')
    await wrapper.find('input[name="password"]').setValue('password')
    await wrapper.find('form').trigger('submit')
    
    expect(wrapper.emitted('success')).toBeTruthy()
})

test('displays error message when login fails', () => {
    const wrapper = mount(LoginForm, {
        props: { error: 'Invalid credentials' }
    })
    
    expect(wrapper.find('[data-test="error"]').text()).toContain('Invalid credentials')
})
```

## Resources

- **[Vitest Documentation](https://vitest.dev/)** — Official Vitest docs
- **[Vue Test Utils](https://test-utils.vuejs.org/)** — Testing library for Vue 3
- **[Testing Library](https://testing-library.com/)** — User-centric testing philosophy
- **[Kent C. Dodds - Testing Best Practices](https://kentcdodds.com/blog/common-mistakes-with-react-testing-library)** — Excellent testing advice (React-focused but principles apply)

Remember: **Good tests make you confident to refactor and add features. Bad tests make you afraid to change code.** Write tests that give you confidence, not anxiety!
