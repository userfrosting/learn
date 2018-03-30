---
title: Running Tests
taxonomy:
    category: docs
---

You can execute tests using the `test` Bakery command.

```bash
$ php bakery test
```

>>>> UserFrosting's built-in integration tests use a temporary in-memory SQLite database.  For testing to run successfully, you must have the `php-sqlite3` package installed and enabled.  Alternatively, you can create a separate testing database and override the `test_integration` database settings in the `testing.php` [environment mode](/configuration/config-files) from your site sprinkle.

The `test` command will fire all tests define in UserFrosting base system along with any tests defined by the sprinkles found in your app. Those tests are executed by [PHPUnit](https://phpunit.de/).
