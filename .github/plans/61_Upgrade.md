# UserFrosting 6.1 Documentation Upgrade Plan

## Scope and source baseline

The 6.1 page tree was copied from 6.0, so update only `app/pages/6.1/`. Do not edit the 6.0 or earlier documentation trees. Compare the [monorepo 6.0-to-6.1 diff](https://github.com/userfrosting/monorepo/compare/6.0...6.1), read the [6.1 changelog](https://github.com/userfrosting/monorepo/blob/6.1/CHANGELOG.md), and verify examples against the 6.1 source files and [Skeleton Docker README](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/docker/README.md).

## Steps and page inventory

### 1. Complete the 6.0-to-6.1 upgrade guide

**Pages to edit**

- `app/pages/6.1/23.upgrading/chapter.md`
- `app/pages/6.1/23.upgrading/01.60-to-61/chapter.md`
- `app/pages/6.1/23.upgrading/01.60-to-61/01.changes/docs.md`
- `app/pages/6.1/23.upgrading/01.60-to-61/02.guide/docs.md`
- `app/pages/6.1/23.upgrading/01.60-to-61/03.docker/docs.md`

**Research and content**

- Summarize the changes under the 6.1.0-beta.1 changelog heading, then use the branch diff to find relevant changes absent from the summary.
- Verify PHP and package version constraints, frontend dependency versions, and changed Skeleton files against [`packages/skeleton/composer.json`](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/composer.json) and [`packages/skeleton/package.json`](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/package.json).
- Explain upgrade sequencing and migration effects: `ActivitiesV2Table` adds polymorphic subject/context data and new activity metadata/properties; group/role soft-delete migrations and anonymization affect reuse of identifiers and audit history.
- Document application/config changes to check: `CSRF_SECRET` and `setup:csrf-secret`; cache-backed CAPTCHA and its disabled-by-default setting; YAML/JSON dictionaries and PHP-array compatibility; the `setup:db` connection option change; `clean:database`, `clean:cache`, and the deprecated `clear-cache` alias.
- Complete the Docker-specific upgrade steps using the 6.1 Skeleton README, including the new root Docker environment file, split Compose configuration, host UID/GID setup, and development versus production environment files.
- Clearly distinguish required migration steps from optional feature/configuration changes, and call out deprecated compatibility paths where relevant.

### 2. Update installation guidance for 6.1

**Pages to edit**

- `app/pages/6.1/01.quick-start/docs.md`
- `app/pages/6.1/04.installation/chapter.md`
- `app/pages/6.1/04.installation/01.requirements/01.basic-stack/docs.md`
- `app/pages/6.1/04.installation/02.environment/docs.md`
- `app/pages/6.1/04.installation/02.environment/01.native/docs.md`
- `app/pages/6.1/04.installation/02.environment/01.native/01.requirements/docs.md`
- `app/pages/6.1/04.installation/02.environment/01.native/02.install/docs.md`
- `app/pages/6.1/04.installation/02.environment/02.docker/docs.md`
- `app/pages/6.1/04.installation/03.basic-cli/docs.md`

**Research and content**

- Set the supported minimum to PHP 8.3, and explain the PHP versions supported by the 6.1 Skeleton. Make PHP 8.5 the recommended version. Its Docker image defaults to PHP 8.5; verify the selectable versions against the Docker README before describing them as recommendations.
- Make Docker the first/recommended setup route while preserving native installation as an alternative.
- Replace `^6.0` project-creation examples with the appropriate 6.1 constraint. Verify the 6.1 Composer and npm commands, required Node version, and `docker compose` minimum version.
- Update Docker setup instructions for `docker/.env.example`, `app/.env.docker`, Compose startup, the initial Bakery setup, and host UID/GID mapping. Keep the development-install instructions separate from the production deployment guide in Step 4.

### 3. Add the Activity subchapter under Chapter 10: Users

**Pages to edit**

- `app/pages/6.1/10.users/chapter.md`
- `app/pages/6.1/10.users/04.activity-logging/docs.md` (clarify how existing user activity logging relates to the 6.1 recorder)
- `app/pages/6.1/06.dependency-injection/03.default-services/docs.md` (verify and document the new injectable recorder if registered as a default service)

**Pages to create**

- `app/pages/6.1/10.users/05.activities/chapter.md`
- `app/pages/6.1/10.users/05.activities/01.concepts/docs.md`
- `app/pages/6.1/10.users/05.activities/02.recording/docs.md`
- `app/pages/6.1/10.users/05.activities/03.displaying-activities/docs.md`

**Research and content**

- Explain the actor (`user`), activity type, optional polymorphic `subject` (what was acted on), and optional `context` (related model); cover `metadata` translation placeholders and `properties` old/new values.
- Verify the recording example against [`ActivityRecorderInterface`](https://github.com/userfrosting/monorepo/blob/6.1/packages/sprinkle-account/app/src/Log/ActivityRecorderInterface.php) and [`ActivityRecorder`](https://github.com/userfrosting/monorepo/blob/6.1/packages/sprinkle-account/app/src/Log/ActivityRecorder.php). Research the required `BackedEnum` activity type, registering types through [`ActivityRecipe`](https://github.com/userfrosting/monorepo/blob/6.1/packages/sprinkle-account/app/src/Sprinkle/Recipe/ActivityRecipe.php), translation keys, nullable/system actors, and when to record a successful change.
- Explain the migration and model relationships using [`Activity.php`](https://github.com/userfrosting/monorepo/blob/6.1/packages/sprinkle-account/app/src/Database/Models/Activity.php) and [`ActivitiesV2Table.php`](https://github.com/userfrosting/monorepo/blob/6.1/packages/sprinkle-account/app/src/Database/Migrations/v610/ActivitiesV2Table.php). Explicitly distinguish the new recorder from the older Monolog user-activity logger and describe compatibility/deprecation accurately.
- For UI display, trace the existing API, authorization, and Pink-Cupcake rendering. Use [`UserActivities.vue`](https://github.com/userfrosting/monorepo/blob/6.1/packages/theme-pink-cupcake/src/components/Pages/Admin/User/UserActivities.vue), [`ActivityDescription.vue`](https://github.com/userfrosting/monorepo/blob/6.1/packages/theme-pink-cupcake/src/components/Activities/ActivityDescription.vue), and the admin activity controllers/routes. Cover built-in user/group/role/permission histories, permission checks, translated descriptions, and how an application can render its own related activity history.

### 4. Add a dedicated “Going Live with Docker” page

**Pages to edit**

- `app/pages/6.1/22.going-live/chapter.md`

**Page to create**

- `app/pages/6.1/22.going-live/06.docker-production/docs.md`

**Research and content**

- Keep this as a production deployment guide, not a second development install guide. Follow the [6.1 Skeleton Docker README](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/docker/README.md) and [`compose.production.yaml`](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/compose.production.yaml).
- Research production-only services, image build behavior and Vite asset compilation, persistent/private database configuration, app environment-file names, Docker environment variables, secrets, and public URL/mail settings.
- Give a safe first-deploy sequence: configure secrets and production mode, build/start containers, run migrations, create the initial admin account, and put a TLS-terminating reverse proxy in front of the web container. Explain why production should use individual Bakery commands rather than `bake`.
- Include the update/maintenance workflow, volume/data cautions, and links to the existing HTTPS and backup pages; review those linked pages’ `outdated: true` status as part of Step 5.

### 5. Run a general 6.1 consistency pass

Audit every Markdown page under `app/pages/6.1/` after the focused updates. The following pages have confirmed 6.1-specific discrepancies or are direct cross-references to them:

- `app/pages/6.1/02.background/05.security/02.csrf-protection/docs.md` — replace the session-bound token explanation with the 6.1 signed double-submit model.
- `app/pages/6.1/07.routes-and-controllers/06.client-input/02.csrf-guard/docs.md` — verify form/AJAX token handling and middleware details against 6.1.
- `app/pages/6.1/09.configuration/01.environment-vars/docs.md` — reconcile `DB_CONNECTION`/`DB_DRIVER` with 6.1 configuration and document `CSRF_SECRET`.
- `app/pages/6.1/13.i18n/01.introduction/docs.md` — update PHP-only language-dictionary descriptions/examples for YAML and JSON support.
- `app/pages/6.1/14.cli/01.commands/docs.md` — verify `setup:db` options, clean/prune commands, cache commands, and the current asset-build commands.
- `app/pages/6.1/18.advanced/05.caching/02.usage/docs.md` — use the current cache-cleaning command and identify the deprecated alias accurately.
- `app/pages/6.1/05.sprinkles/03.recipe/docs.md`
- `app/pages/6.1/05.sprinkles/04.customize/docs.md`
- `app/pages/6.1/05.sprinkles/05.community/docs.md` — verify version-specific Composer examples against the 6.1 Skeleton.
- `app/pages/6.1/22.going-live/01.hosting/docs.md`
- `app/pages/6.1/22.going-live/03.vps-production-environment/01.server-setup/docs.md`
- `app/pages/6.1/22.going-live/03.vps-production-environment/03.application-setup/docs.md` — correct the PHP minimum and verify the legacy production asset/build commands against the 6.1 workflow.

Also review, without assuming a full rewrite, the existing `outdated: true` Going Live pages: `02.mail-providers/docs.md`, `04.backup/docs.md`, `03.vps-production-environment/02.git-for-deployment/docs.md`, `04.ssl/docs.md`, `05.additional-recommendations/docs.md`, and `05.unix-primer-ubuntu/docs.md`. Review the already-obsolete Webpack Encore chapter and the obsolete Recipes chapter for broken links or content presented as current; do not silently turn this 6.1 release task into a wholesale rewrite of those historical guides.

**Validation**

- Re-check all changed examples against the 6.1 branch sources, including version constraints and command names.
- Verify internal links resolve within the 6.1 page tree, existing image paths are valid, and Docker development and production instructions are not conflated.
- Keep any additional general-pass edits limited to verified 6.1 inaccuracies and files under `app/pages/6.1/`.

## Key upstream references

- [Monorepo 6.0...6.1 comparison](https://github.com/userfrosting/monorepo/compare/6.0...6.1)
- [Monorepo CHANGELOG.md](https://github.com/userfrosting/monorepo/blob/6.1/CHANGELOG.md)
- [Skeleton Docker README](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/docker/README.md)
- [Skeleton Composer manifest](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/composer.json)
- [Skeleton frontend package manifest](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/package.json)
- [Production Compose entrypoint](https://github.com/userfrosting/monorepo/blob/6.1/packages/skeleton/compose.production.yaml)
- Activity API, migration, and UI references are linked in Step 3 above.
