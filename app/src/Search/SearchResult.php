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

use JsonSerializable;

/**
 * Represents a search result with snippet.
 */
readonly class SearchResult implements JsonSerializable
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $route,
        public string $snippet,
        public int $score,
        public string $version,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'title'   => $this->title,
            'slug'    => $this->slug,
            'route'   => $this->route,
            'snippet' => $this->snippet,
            'score'   => $this->score,
            'version' => $this->version,
        ];
    }
}
