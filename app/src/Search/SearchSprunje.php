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

use Illuminate\Support\Collection;
use InvalidArgumentException;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * SearchSprunje
 *
 * Provides a Sprunje-compatible interface for searching documentation pages.
 * Adapts the SearchService to work with the Sprunje API.
 *
 * @phpstan-import-type IndexedPage from IndexedPageShape
 * @phpstan-import-type SearchResult from IndexedPageShape
 *
 * @extends StaticSprunje<array{
 *     query: string,
 *     version: string|null,
 *     size: string|int|null,
 *     page: string|int|null,
 * }, SearchResult>
 */
class SearchSprunje extends StaticSprunje
{
    public function __construct(
        protected SearchService $searchService,
        protected SearchIndex $searchIndex,
        protected Config $config
    ) {
    }

    /**
     * Get the underlying queryable object in its current state.
     *
     * @return Collection<int, SearchResult>
     */
    public function getQuery(): Collection
    {
        // No version specified means no results
        if ($this->options['version'] === null) {
            return collect([]);
        }

        // Get the index from cache
        $index = $this->searchIndex->getIndex($this->options['version']);

        // No indexed pages means no results
        if (count($index) === 0) {
            return collect([]);
        }

        // Search through the index (without pagination - Sprunje handles that)
        $results = $this->searchService->performSearch($this->options['query'], $index);

        // Convert to Collection for compatibility
        $collection = collect($results);

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    protected function validateOptions(array $options): array
    {
        // Validate query length
        $minLength = $this->config->get('learn.search.min_length', 3);
        if (!is_string($options['query']) || $options['query'] === '' || mb_strlen($options['query']) < $minLength) {
            throw new InvalidArgumentException("Query must be at least {$minLength} characters long");
        }

        // Define default options
        $options['version'] ??= $this->config->get('learn.versions.latest');
        $options['size'] ??= $this->config->get('learn.search.default_size', 'all');
        $options['page'] ??= $this->config->get('learn.search.default_page', null);

        return parent::validateOptions($options);
    }
}
