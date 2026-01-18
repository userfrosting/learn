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

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
        
        // Call parent constructor which will initialize the query via baseQuery()
        parent::__construct($options);
    }

    /**
     * Required by Sprunje abstract class. Returns a dummy Eloquent builder.
     * 
     * SearchSprunje doesn't use database queries - we override getModels() 
     * to use SearchService directly. This builder is never actually used for queries.
     *
     * @return EloquentBuilder
     */
    protected function baseQuery(): EloquentBuilder
    {
        // Return a dummy Eloquent builder that won't be used
        // We use a simple Eloquent model just to satisfy the type requirement
        $model = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'dummy';
        };
        return $model::query();
    }

    /**
     * Override getModels to use SearchService instead of database queries.
     *
     * @return array{int, int, Collection<int, array>}
     */
    public function getModels(): array
    {
        // Get the version to search
        $versionId = $this->version ?? $this->config->get('learn.versions.latest');
        
        if ($versionId === null) {
            return [0, 0, collect([])];
        }

        // Get the index from cache
        $index = $this->searchService->getIndex($versionId);

        if (count($index) === 0) {
            return [0, 0, collect([])];
        }

        // Search through the index (without pagination - Sprunje handles that)
        $results = $this->searchService->performSearch($this->searchQuery, $index);

        // Convert to Collection for compatibility
        $collection = collect($results);

        return [
            count($index),
            count($results),
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
