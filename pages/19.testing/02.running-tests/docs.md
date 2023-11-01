---
title: Running Tests
taxonomy:
    category: docs
---

You can execute tests using [Phpunit](https://phpunit.de) directly : 

```bash
./vendor/bin/phpunit
```

UserFrosting's built-in integration tests use a temporary in-memory SQLite database. For testing to run successfully, you must have the `php-sqlite3` package installed and enabled. Alternatively, you can create a separate testing database and override the `test_integration` database settings in the `testing.php` [environment mode](/configuration/config-files) from your site sprinkle.

When testing, only the tests define in your Sprinkle will be run. UserFrosting base system tests are run in their own repository.
