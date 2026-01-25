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
 * Represents a page indexed for search.
 */
readonly class IndexedPage
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $route,
        public string $content,
        public string $version,
        public string $keywords,
        public string $metadata,
    ) {
    }
}
