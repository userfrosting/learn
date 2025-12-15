---
title: Helper Traits & Class
metadata:
    obsolete: true
---

UserFrosting provides some helper Traits to easily enable features and tools for your tests. Some of those tools make it easier to test your code against a testing database.

## ContainerStub

Sometime you need the container, but don't want the *whole* container. You might even want to set stub services. This is were the `UserFrosting\Testing\ContainerStub` comes in. This class can be used to create an empty PHP-DI container. You can then use [PHP-DI getter and setter function](https://php-di.org/doc/container.html) to set your test services on the ContainerStub.

```php
use UserFrosting\Testing\ContainerStub;
use App\MySite\Foo;

// ...

$ci = ContainerStub::create();
$foo = = Mockery::mock(Foo::class)
$ci->set(Foo::class, $foo);

// ...

$ci->get(Foo::class);
```

The `create` method can also accept services definition: 

```php
$provider = new AlertStreamService();
$ci = ContainerStub::create($provider->register());
```

## BakeryTester

`UserFrosting\Testing\BakeryTester` can be used to test bakery commands. This class has one static command, and returns the result of the executed command. 

```php
public static function runCommand(
    Command $command,
    array $input = [],
    array $userInput = [],
    int $verbosity = OutputInterface::VERBOSITY_NORMAL,
): CommandTester
```

The `$command` argument must be an instance of the command class. `$input` is an array of command arguments and options. `$userInput` is an Array of strings representing each input passed to the command input stream.

**Example:**
```php
use UserFrosting\Testing\BakeryTester;

// ... 

$command = $this->ci->get(ClearCacheCommand::class);
$result = BakeryTester::runCommand($command);
```

The result is an instance of [Symfony's `CommandTester`](https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Console/Tester/CommandTester.php), which have some methods you can assert on :
- `getDisplay` : Gets the display returned by the last execution of the command.
- `getErrorOutput` : Gets the output written to STDERR by the application.
- `getStatusCode` : Gets the status code returned by the last execution of the command or application.
- `assertCommandIsSuccessful` : Assert command is successful.

**Example:**
```php
$this->assertSame(0, $result->getStatusCode());
$this->assertStringContainsString('Cache cleared', $result->getDisplay());
```

## RefreshDatabase

By default all tests are run against an in-memory SQLite database. This database is temporary and independent from the database used by your UserFrosting instance. That means your data is safe when tests are run. If you prefer to use a real database for tests, you can overwrite the `test_integration` connection config in your own sprinkle for the `testing` environment.

> [!WARNING]
> While you **can** test your code against the main database, it usually not a good idea to do so with a production database. Those are _tests_ after all. They _can_ fail. **Catastrophically**. UserFrosting built-in tests are all run against a test database.

Note that the in-memory database is empty by default. If your test requires the standard tables to be up, you need to use the `UserFrosting\Sprinkle\Core\Testing\RefreshDatabase` trait to run all migrations up. You could also use the migrator service to run a particular migration up.

To use, you need to add the `RefreshDatabase` trait, and call `$this->refreshDatabase();` in your test setup:

```php
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

// ...

class MyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->refreshDatabase();
    }
}
```

It's good practice to reset your database before each test so that data from a previous test does not interfere with your tests. The `RefreshDatabase` trait will help you wipe the database clean and run all migration up.

> [!WARNING]
> This is **destructive**! All existing data in the database will be lost. Use it along the in-memory SQLite database to avoid losing data in your production database

## WithTestUser

This trait contains many useful methods for tests that require an actual user. To use any of the methods, you first need to add the `UserFrosting\Sprinkle\Account\Testing\WithTestUser` trait to your class. This trait add a single public method, `actAsUser`:

```php
protected function actAsUser(
    UserInterface $user,
    bool $isMaster = false,
    array $roles = [],
    array $permissions = []
)
```

The method accept a UserInterface class. Optionally, you can use the `$isMaster` to force the user to be a master user (useful to bypass any permission checks!), pass roles to assign to this user (as an array of `RoleInterface`), or permissions (as an array of `PermissionInterface` or permissions slugs).

**Example: Create a user using Factories, and assign `test_permissions`**
```php
/** @var User */
$user = User::factory()->create();

$this->actAsUser($user, permissions: ['test_permissions']);
```

> [!NOTE]
> When dealing with logged-in user, they will be automatically logout at the end of the test to avoid collision with subsequent tests.
