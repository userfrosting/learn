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

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Support\Collection;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * SearchSprunje
 *
 * Provides a Sprunje-compatible interface for searching documentation pages.
 * Adapts the SearchService to work with the Sprunje API.
 */
class SearchSprunje extends Sprunje
{
    /**
     * @var string Name of this Sprunje
     */
    protected string $name = 'search';

    /**
     * @var string The search query
     */
    protected string $searchQuery = '';

    /**
     * @var string|null The version to search
     */
    protected ?string $version = null;

    public function __construct(
        protected SearchService $searchService,
        protected Config $config,
        array $options = []
    ) {
        // Extract search-specific options before passing to parent
        $this->searchQuery = $options['query'] ?? '';
        $this->version = $options['version'] ?? null;
        
        // Validate query here for consistency
        $minLength = $this->config->get('learn.search.min_length', 3);
        if ($this->searchQuery === '' || mb_strlen($this->searchQuery) < $minLength) {
            throw new \InvalidArgumentException("Query must be at least {$minLength} characters long");
        }
        
        // Remove search-specific options before parent processes them
        unset($options['query'], $options['version']);
        
        parent::__construct($options);
    }

    /**
     * Required by Sprunje abstract class, but not used for search functionality.
     * 
     * SearchSprunje uses SearchService instead of Eloquent queries, so this method
     * is not called. We override getModels() to bypass the database query system.
     * 
     * @throws \RuntimeException if accidentally called
     * @return EloquentBuilderContract|QueryBuilderContract
     */
    protected function baseQuery(): EloquentBuilderContract|QueryBuilderContract
    {
        // This should never be called since we override getModels().
        // If it is called, it indicates a problem with our implementation.
        throw new \RuntimeException('SearchSprunje does not use database queries. Use getModels() directly.');
    }

    /**
     * Override getModels to use SearchService instead of database queries.
     *
     * @return array{int, int, Collection<int, array>}
     */
    public function getModels(): array
    {
        // Get pagination parameters
        $page = $this->options['page'] ?? 1;
        $size = $this->options['size'] ?? $this->config->get('learn.search.default_size', 10);
        
        // Handle 'all' size
        if ($size === 'all') {
            $size = $this->config->get('learn.search.max_results', 1000);
            $page = 1; // Start at page 1, not 0
        } else {
            $size = (int) $size;
            $page = (int) $page;
        }

        // Perform search via SearchService
        $result = $this->searchService->search($this->searchQuery, $this->version, $page, $size);

        // Convert to Collection for compatibility
        $collection = collect($result['rows']);

        return [
            $result['count'],
            $result['count_filtered'],
            $collection,
        ];
    }

    /**
     * Override validateOptions to include search-specific validation.
     *
     * @param mixed[] $options
     */
    protected function validateOptions(array $options): void
    {
        // Don't validate query and version here as they're handled separately
        $optionsToValidate = $options;
        unset($optionsToValidate['query'], $optionsToValidate['version']);
        
        parent::validateOptions($optionsToValidate);
    }
}
