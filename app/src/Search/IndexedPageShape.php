<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Search;

/**
 * Shared type definitions for search functionality.
 *
 * This interface defines the structure of indexed pages and search results.
 * It uses @phpstan-type to create reusable type aliases that can be
 * imported by other classes.
 *
 * Page indexed for search.
 * @phpstan-type IndexedPage array{
 *     title: string,
 *     slug: string,
 *     route: string,
 *     content: string,
 *     version: string,
 *     keywords: string,
 *     metadata: string
 * }
 *
 * Search result with snippet.
 * @phpstan-type SearchResult array{
 *     title: string,
 *     slug: string,
 *     route: string,
 *     snippet: string,
 *     matches: int,
 *     version: string
 * }
 */
interface IndexedPageShape
{
}
