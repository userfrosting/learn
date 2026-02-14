---
title: Running Tests
description: How to execute your PHPUnit and Vitest tests, both locally and in CI/CD pipelines.
---

UserFrosting provides two test suites: **PHPUnit** for backend PHP code and **Vitest** for frontend Vue/TypeScript code. Here's how to run both.

## Backend Tests (PHPUnit)

You can execute backend tests using [PHPUnit](https://phpunit.de) directly:

```bash
./vendor/bin/phpunit
```

Or use the predefined VS Code task:

- Open Command Palette (`Cmd+Shift+P` on macOS, `Ctrl+Shift+P` on Windows/Linux)
- Run `Tasks: Run Task`
- Select `Backend - PHPUnit`

UserFrosting's built-in integration tests use a temporary in-memory SQLite database. For testing to run successfully, you must have the `php-sqlite3` package installed and enabled. Alternatively, you can create a separate testing database and override the `test_integration` database settings in the `testing.php` [environment mode](/configuration/config-files) from your site sprinkle.

When testing, only the tests defined in your app will be run. UserFrosting base system tests (located in `/vendor`) are run in their own repository.

## Frontend Tests (Vitest)

Execute frontend tests using npm:

```bash
npm run test
```

Generate a coverage report:

```bash
npm run coverage
```

Or use the predefined VS Code task:

- Open Command Palette
- Run `Tasks: Run Task`
- Select `Frontend - Tests` or `Frontend - Coverage`

Coverage reports are typically generated in `_meta/coverage/` or `_meta/_coverage/` and can be viewed in your browser.

> [!TIP]
> During development, use watch mode (`npm run test:watch`) to get instant feedback as you write code. Vitest will automatically re-run affected tests when you save files.

## Continuous Integration

In CI/CD pipelines, run both test suites:

```bash
# Backend tests
./vendor/bin/phpunit

# Frontend tests
npm run test

# Optional: Generate coverage reports
npm run coverage
```

Many teams configure CI to fail the build if test coverage drops below a certain threshold.
