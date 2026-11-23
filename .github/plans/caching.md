# Documentation page loading and caching

## Problem and findings

- `DocumentationRepository::getTree()` and `getPage()` both call `getPages(..., false)`. `PageFactory::createFromResource()` reads and converts each Markdown file immediately.
- `TwigGlobals` loads the complete tree before the controller resolves the requested page. A normal page request therefore parses the whole version’s documentation for the menu, then repeats the work to find the current page. The 6.0 tree currently contains 179 Markdown files (~11 MB).
- Learn’s cache is disabled in default configuration. Production enables it, but separate tree and page cache entries still leave duplicated work on cache misses.
- Development Compose defaults the framework cache driver to `file`; Redis/Memcached services are optional profiles and do not by themselves enable Learn’s documentation cache.

## Proposed approach

Separate navigation metadata from rendered Markdown with the following concrete flow:

1. In `PageFactory`, add a metadata-only creation path. Read each resource’s front-matter block and parse it with the CommonMark front-matter parser already used by the configured Markdown extension; derive slug, parent slug, route, title, and resource URI from that data and the resource path. Do not invoke the full Markdown converter while building navigation.
2. Add a small `PageMetadata` value object carrying that navigation data and children. In `DocumentationRepository`, sort the metadata list once, build a slug-to-metadata index, and attach child entries through their parent slugs. `getTree()`, breadcrumbs, and previous/next navigation then reuse this index rather than repeatedly filtering or scanning all full pages.
3. Resolve a request’s slug through the index and use its resource URI to load and convert only that Markdown file into the existing full `PageResource`. Keep the current empty-slug/home behavior based on the first path-sorted entry.
4. In development, rebuild metadata from the bind-mounted files on each request and do not cache rendered page content, so changing, adding, removing, or renaming a page is reflected on the next request without cache pruning. Do not add a watcher initially.
5. In production, retain the existing enabled cache and backend selection, but cache serializable metadata and per-page rendered output under versioned keys rather than caching resource-locator objects. Add a `docs:cache` Bakery command to warm each version’s tree and full-page entries. Register it next to the existing `search:index` command and add it to production startup after `clean:cache`; run `search:index` after `docs:cache` so index building can reuse the warmed page payloads. Retain the existing TTL as a fallback.

Search indexing remains an intentional full-content path: enumerate metadata, resolve each corresponding resource, and convert every page while rebuilding the search index. This keeps the normal request lazy without dropping searchable page content.

The cache-warming command should follow the `SearchIndexCommand` pattern: accept an optional `--doc-version` (default to all available versions), report the number of pages/versions warmed, and return a failure exit code with a clear error if warming fails. It should show a Symfony Console progress bar with a known page total, advancing once per full-page cache entry warmed and identifying the version being processed. Its repository operation should populate both tree metadata and full-page entries through the same methods used by requests, rather than duplicating cache-key construction in the command.

Search indexing is an intentional exception: it needs every page’s content, so it should resolve metadata entries and load/convert each page while building the index. Preserve sidebar getters so templates need no changes unless implementation proves otherwise.

Do not add a watcher in the first iteration. The current development setup bind-mounts the source, so a watcher would add invalidation and cross-platform complexity; consider it only if measuring metadata discovery shows it is still a bottleneck. Benchmark before/after and confirm page rendering, tree updates, breadcrumbs, previous/next navigation, and search indexing.

## Files expected to change

- `app/src/Documentation/PageMetadata.php` (new): lightweight, serializable navigation entry with the getters used by the sidebar.
- `app/src/Documentation/DocumentationRepository.php`: build/cache metadata tree and slug index; load the requested full page only; derive breadcrumbs and adjacent-page links from metadata.
- `app/src/Documentation/PageFactory.php`: create metadata without full Markdown conversion and keep full conversion for a single page load.
- `app/src/Twig/Extensions/FileTreeExtension.php`: update page type hints to the metadata type while retaining active-child behavior.
- `app/src/Search/SearchIndex.php`: load full page content from metadata only during index builds.
- `app/src/Bakery/DocumentationCacheCommand.php` (new): provide the `docs:cache` command for warming tree and page caches by version.
- `app/src/Recipe.php` and `app/src/Bakery/BakeCommandListener.php`: register the new command alongside `search:index`.
- `app/tests/Documentation/DocumentationRepositoryTest.php`, `app/tests/Controller/DocumentationControllerTest.php`, and `app/tests/Search/SearchIndexTest.php`: regression coverage for fresh metadata, page rendering, navigation, and search.
- `app/tests/Bakery/DocumentationCacheCommandTest.php` (new), `app/tests/Bakery/BakeryListenersTest.php`, and `app/tests/RecipeTest.php`: verify command execution, registration, and failure handling.
- `docker/compose.production.app.yaml`: after clearing stale cache, warm docs/page caches and then build the search index before starting PHP-FPM.
- `docker/README.md`: document the production cache profiles, cache-warming commands, and live-edit development behavior.

The existing Twig sidebar templates should remain unchanged if `PageMetadata` preserves the methods they call. No Compose watcher or cache-driver configuration change is planned initially; `default.php` already disables Learn caching, and `production.php` already enables it. Revisit those files only if implementation or measurements show that existing settings are insufficient.

## Implementation todos

Tracked in the session SQL todo list:

1. Establish representative request-time and Markdown-conversion baselines.
2. Introduce lightweight page metadata and refactor tree construction, lookup, and navigation to avoid parsing every page per request.
3. Keep development metadata live and add production caching for serializable metadata/page output with safe invalidation.
4. Add and register `docs:cache` to warm the complete page and tree cache alongside `search:index`, then invoke both during production startup.
5. Add focused regression tests, update Docker/cache guidance, and compare benchmark results.

## Notes

- Preserve ordering, latest/versioned routes, home-page behavior, and search indexing.
- Prefer serializable cache payloads rather than assuming `PageResource` and its resource-locator objects can be safely shared by all cache drivers.
- Validate both development edit freshness (including title/tree changes) and production cache reuse; compare cold and warm request costs against the baseline.

## References

- [Laravel cache documentation](https://laravel.com/framework/docs/10.x/cache) describes the unified `remember` API and file, Redis, and Memcached store options.
- [Docker Compose Watch documentation](https://docs.docker.com/compose/how-tos/file-watch/) describes file synchronization and restart actions; Compose Watch is separate from application-cache invalidation.
