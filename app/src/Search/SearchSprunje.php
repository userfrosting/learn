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
 * @extends StaticSprunje<SearchResult>
 */
class SearchSprunje extends StaticSprunje
{
    /** @var string|null Documentation version to search */
    protected ?string $version = null;

    /** @var string Search query */
    protected string $query = '';

    public function __construct(
        protected SearchService $searchService,
        protected SearchIndex $searchIndex,
        protected Config $config
    ) {
        // Set default size and version from config
        $this->size = $this->config->getInt('learn.search.default_size', 10);
        $this->version = $this->config->getString('learn.versions.latest');
    }

    /**
     * Set the search query.
     *
     * @param string $query Search query
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public function setQuery(string $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Set the documentation version.
     *
     * @param string|null $version Documentation version
     *
     * @return static
     */
    public function setVersion(?string $version): static
    {
        $this->version = $version ?? $this->config->get('learn.versions.latest');

        return $this;
    }

    /**
     * Get the documentation version.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Get the base collection of items to process.
     *
     * @return Collection<int, SearchResult>
     */
    public function getItems(): Collection
    {
        // No version specified means no results
        if ($this->version === null) {
            return collect([]);
        }

        // Get the index from cache
        $index = $this->searchIndex->getIndex($this->version);

        // No indexed pages means no results
        if (count($index) === 0) {
            return collect([]);
        }

        // Search through the index (without pagination - Sprunje handles that)
        $results = $this->searchService->performSearch($this->query, $index);

        // Convert to Collection for compatibility
        $collection = collect($results);

        return $collection;
    }
}
