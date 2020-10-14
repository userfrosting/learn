---
title: Helper Traits
taxonomy:
    category: docs
---

UserFrosting provides some helper Traits to easily enable features and tools for your tests. Some of those tools make it easier to test your code against a testing database.

## TestDatabase

This trait can be used force the `test_integration` database connection to be used for testing. This means all tests will be run against an in-memory SQLite database. This database is temporary and independent from the database used by your UserFrosting instance. That means your data is safe when tests are run. If you prefer to use a real database for tests, you can overwrite the `test_integration` connection config in your own sprinkle for the `testing` environment.

[notice=warning]While you **can** test your code against the main database, it usually not a good idea to do so with a production database. Those are _tests_ after all. They _can_ fails. Catastrophically. UserFrosting built-in tests are all run against a test database.[/notice]

Note that the in-memory database is empty by default. If your test requires the standard tables to be up, you can use the `RefreshDatabase` trait to run all migrations up. You could also use the migrator service to run a particular migration up.

To use, you need to add the `TestDatabase` trait, and call `$this->setupTestDatabase();` in your test setup:

```php
class MyTest extends TestCase
{
    use TestDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
    }
}
```

## RefreshDatabase

It's good practice to reset your database before each test so that data from a previous test does not interfere with your tests. The `RefreshDatabase` trait will help you wipe the database clean and run all migration up.

[notice=warning]This is **destructive**! All existing data in the database will be lost. Use it along the `TestDatabase` trait to avoid losing data in your production database ![/notice]

To use, you need to add the `RefreshDatabase` trait, and call `$this->refreshDatabase();` in your test setup:

```php
class MyTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }
}
```

## withTestUser

This trait contains many useful methods for tests that require an actual user. To use any of the methods, you first need to add the `withTestUser` trait to your class.


| Class                                                                          | Description                                                           |
| ------------------------------------------------------------------------------ | --------------------------------------------------------------------- |
| `loginUser(UserInterface $user)`                                               | Login the provided user object                                        |
| `logoutCurrentUser`                                                            | Logout the currently logged user                                      |
| `createTestUser($isMaster = false, $login = false, array $params = [])`        | Create a test user. Use arguments to make it a master user, to log him in and set any user parameter. |
| `getRandomUserId($masterId)`                                                   | Returns a random user id, exclusing th master id                      |
| `giveUserTestPermission(UserInterface $user, $slug, $conditions = 'always()')` | Gives a user a new test permission                                    |
| `giveUserPermission(UserInterface $user, Permission $permission)`              | Add the test permission to a Role, then the role to the user          |

[notice]When dealing with logged-in user, they will be automatically logout at the end of the test to avoid collision with subsequent tests.[/notice]

## withDatabaseSessionHandler

This trait needs to be included if you want to test the `database` session handler. This trait should be used with `TestDatabase` and `RefreshDatabase`.

To use, you need to add the `withDatabaseSessionHandler` trait, and call `$this->useDatabaseSessionHandler();` in your test:

```php
class MyTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;
    use withDatabaseSessionHandler;

    public function testSomethingWithSessionDatabase()
    {
        // Reset CI Session
        $this->useDatabaseSessionHandler();

        // ...
    }
}
```
