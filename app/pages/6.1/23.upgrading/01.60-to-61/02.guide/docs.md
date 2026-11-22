---
title: Upgrade Guide
description: Update guide for UserFrosting 6.1
---

## File replacements and updates

### PHP
- Replace with the updated version : 
  - `.gitignore`
  - `.vscode/tasks.json`
  - `.vscode/settings.json`
  - `app/tests/Bakery/HelloCommandTest.php`
  - `/public/index.php`
- Replace locale files : 
  - `app/locale/en_US/messages.php` -> `app/locale/en_US/messages.yaml`
  - `app/locale/fr_FR/messages.php` -> `app/locale/fr_FR/messages.yaml`

### Composer
Update `require` in `composer.json` : 
```diff
- "php": "^8.1",
- "userfrosting/framework": "^6.0",
- "userfrosting/sprinkle-core": "^6.0",
- "userfrosting/sprinkle-account": "^6.0",
- "userfrosting/sprinkle-admin": "^6.0"
+ "php": "^8.3",
+ "userfrosting/framework": "^6.1",
+ "userfrosting/sprinkle-core": "^6.1",
+ "userfrosting/sprinkle-account": "^6.1",
+ "userfrosting/sprinkle-admin": "^6.1"
```

Update `require-dev` in `composer.json` : 
```diff
- "phpstan/phpstan": "^1.1",
- "phpstan/phpstan-deprecation-rules": "^1.0",
- "phpstan/phpstan-mockery": "^1.0",
- "phpstan/phpstan-phpunit": "^1.0",
- "phpstan/phpstan-strict-rules": "^1.0",
+ "phpstan/phpstan": "^2.2",
+ "phpstan/phpstan-deprecation-rules": "^2.0",
+ "phpstan/phpstan-mockery": "^2.0",
+ "phpstan/phpstan-phpunit": "^2.0",
+ "phpstan/phpstan-strict-rules": "^2.0",
```

### Javascript
- Update `package.json` dependencies for UserFrosting packages to `^6.1`:
  - `@userfrosting/sprinkle-account`
  - `@userfrosting/sprinkle-admin`
  - `@userfrosting/sprinkle-core`
  - `@userfrosting/theme-pink-cupcake`
- Update `package.json` dependencies for other packages if needed:
  - `"vite": "^8.0.16",` -> `"vite": "^8.3.3",`

### Docker
- Replace the `docker/` directory with the updated Docker directory
- Replace `compose.yml` with the updated `compose.yaml` (Note: the new format uses `.yaml` instead of `.yml`!)
- Replace with the updated version:
  - `app/.env.docker`
  - `app/.env.docker`
  - `app/.env.example`
- Copy new files :
  - `.dockerignore`
  - `compose.production.yaml`
  - `compose.development.yaml`
  - `app/.env.production.docker`

### Github Actions
- Replace with the updated version:
  - `.github/workflows/Frontend.yml`
  - `.github/workflows/PHPStan.yml`
  - `.github/workflows/PHPUnit.yml`

## File updates

<!-- 
TODO : 

- Migrate locale files from PHP to YAML
-->
