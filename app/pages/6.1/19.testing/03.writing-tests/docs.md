---
title: Writing Your Own Tests
description: Learn how to write PHPUnit tests for your UserFrosting application with helper classes and traits.
---

Writing tests in UserFrosting follows standard PHPUnit conventions, but with additional helper classes and traits to make testing easier. Tests should be placed in a `tests/` directory within your sprinkle.

## Test Organization

Organize your tests into logical directories:

```
app/
  tests/
    Unit/           # Unit tests for isolated components
    Integration/    # Integration tests with database, services
    Controller/     # Tests for route handlers
    Bakery/         # Tests for CLI commands
```

## Basic Test Structure

A basic test extends `UserFrosting\Testing\TestCase` and defines your main sprinkle:

```php
<?php

namespace App\MySite\Tests;

use App\MySite\MySite;
use UserFrosting\Testing\TestCase;

class MyFeatureTest extends TestCase
{
    protected string $mainSprinkle = MySite::class;

    public function testSomething(): void
    {
        // Your test assertions here
        $this->assertTrue(true);
    }
}
```

## Writing Effective Tests

- **Test one thing**: Each test method should verify a single behavior
- **Use descriptive names**: Test method names should clearly describe what is being tested
- **Arrange-Act-Assert**: Structure tests in three clear phases
- **Use factories**: Generate test data with model factories instead of manual creation
- **Clean up**: Use `RefreshDatabase` trait to ensure a clean state between tests

## Next Steps

The following sections provide detailed information on specific testing topics:

- **[Sprinkle Test Case](/testing/writing-tests/testcase)**: Learn about the base test case and helper methods
- **[Helper Traits & Class](/testing/writing-tests/traits)**: Discover testing utilities like `RefreshDatabase` and `WithTestUser`
- **[Factories](/testing/writing-tests/factories)**: Generate realistic test data efficiently

For more advanced testing patterns and examples, refer to UserFrosting's own test suite in the [monorepo](https://github.com/userfrosting/monorepo).
