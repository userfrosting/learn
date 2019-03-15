---
title: Helper Traits
taxonomy:
    category: docs
---

UserFrosting provides some helper Traits to easily enable features and tools for your tests. Some of those tools make it easier to test your code against a testing database.

>>>> While you **can** test your code against the main database (it actually the default behaviour), it usually not a good idea to do so with a production database. Those are tests after all. They _can_ fails. Catastrophically. UserFrosting built-in tests are all run against a test database.

To enable one feature, simply add the Trait to your class. For example :

```php
<?php

use UserFrosting\Tests\TestCase;
use UserFrosting\Tests\WithTestDatabase;

/**
 * MyTest class.
 */
class MyTest extends TestCase
{
    use WithTestDatabase;

    function myTest()
    {

    }
```

## `WithTestDatabase`

This trait will force the `test_integration` database connection to be used for testing. This means all tests will be run against an in-memory SQLite database. This database is temporary and independent from the database used by your UserFrosting instance. That means your data is safe when tests are run. If you prefer to use a real database for tests, you can overwrite the `test_integration` connection config in your own sprinkle for the `testing` environment.

Note that the in-memory database is empty by default. If your test requires the standard tables to be up, you can use the `RefreshDatabase` trait to run all migrations up. You could also use the migrator service to run a particular migration up.

>>>>> While it's generaly not a good idea to run test against a real database, there might be time where it is necessary. The `DatabaseTransactions` trait can help you when dealing with an actual database.

## `RefreshDatabase`

It is often useful to reset your database after each test so that data from a previous test does not interfere with subsequent tests. The `RefreshDatabase` trait will wipe the database clean between each test and run all migration up. Simply use the trait on your test class and everything will be handled for you.

>>>> This trait is destructive! All existing data in the database will be lost. Use it along the `WithTestDatabase` trait to avoid losing data in your production database ! If you need

## `DatabaseTransactions`

This trait will wrap each operation in your tests in a transaction. This means the actions applied to your database will be automatically be cancelled when the test is over. Be careful as this Trait will only enable transactions on the main database connection.

>>>>> Migration and tables manipulations are not covered by transactions.
