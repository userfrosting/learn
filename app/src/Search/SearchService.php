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

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Config\Config;

/**
 * Service for searching documentation pages.
 *
 * Performs searches against the indexed documentation content with:
 * - Wildcard pattern matching
 * - Snippet extraction with context
 * - Pagination support
 * - Result caching
 */
class SearchService
{
    public function __construct(
        protected Cache $cache,
        protected Config $config,
    ) {
    }

    /**
     * Search for a query in the documentation for a specific version.
     *
     * @param string      $query   The search query (supports wildcards: * and ?)
     * @param string|null $version The version to search in, or null for latest
     * @param int         $page    The page number (1-indexed)
     * @param int         $perPage Number of results per page
     *
     * @return array{rows: array, count: int, count_filtered: int}
     */
    public function search(string $query, ?string $version, int $page = 1, int $perPage = 10): array
    {
        // Get the version to search
        $versionId = $version ?? $this->config->get('learn.versions.latest');
        
        if ($versionId === null) {
            return [
                'rows'           => [],
                'count'          => 0,
                'count_filtered' => 0,
            ];
        }

        // Check cache for this search
        $cacheKey = $this->getResultsCacheKey($query, $versionId, $page, $perPage);
        $cached = $this->cache->get($cacheKey);
        
        if (is_array($cached)) {
            return $cached;
        }

        // Get the index from cache
        $index = $this->getIndex($versionId);

        if (count($index) === 0) {
            return [
                'rows'           => [],
                'count'          => 0,
                'count_filtered' => 0,
            ];
        }

        // Search through the index
        $results = $this->performSearch($query, $index);

        // Paginate results
        $totalResults = count($results);
        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($results, $offset, $perPage);

        $response = [
            'rows'           => $paginatedResults,
            'count'          => count($index),
            'count_filtered' => $totalResults,
        ];
        
        // Cache the results
        $ttl = $this->config->get('learn.search.results_cache_ttl', 3600);
        $this->cache->put($cacheKey, $response, $ttl);

        return $response;
    }

    /**
     * Perform the actual search and generate results with snippets.
     *
     * @param string                                                                                                          $query
     * @param array<int, array{title: string, slug: string, route: string, content: string, version: string, keywords: string, metadata: string}> $index
     *
     * @return array<int, array{title: string, slug: string, route: string, snippet: string, matches: int, version: string}>
     */
    protected function performSearch(string $query, array $index): array
    {
        $results = [];
        $query = trim($query);

        if ($query === '') {
            return $results;
        }

        // Determine if query contains wildcards (check once before loop)
        $hasWildcards = str_contains($query, '*') || str_contains($query, '?');

        // Pre-compile regex for wildcard searches to avoid recompiling in loop
        $wildcardRegex = null;
        if ($hasWildcards) {
            $pattern = preg_quote($query, '/');
            $pattern = str_replace(['\*', '\?'], ['.*', '.'], $pattern);
            $wildcardRegex = '/' . $pattern . '/i';
        }

        foreach ($index as $page) {
            $titleMatches = [];
            $keywordMatches = [];
            $metadataMatches = [];
            $contentMatches = [];

            // Search in different fields with priority
            if ($hasWildcards) {
                $titleMatches = $this->searchWithWildcard($wildcardRegex, $page['title']);
                $keywordMatches = $this->searchWithWildcard($wildcardRegex, $page['keywords']);
                $metadataMatches = $this->searchWithWildcard($wildcardRegex, $page['metadata']);
                $contentMatches = $this->searchWithWildcard($wildcardRegex, $page['content']);
            } else {
                $titleMatches = $this->searchPlain($query, $page['title']);
                $keywordMatches = $this->searchPlain($query, $page['keywords']);
                $metadataMatches = $this->searchPlain($query, $page['metadata']);
                $contentMatches = $this->searchPlain($query, $page['content']);
            }

            // Calculate weighted score: title > keywords > metadata > content
            $score = count($titleMatches) * 10 + count($keywordMatches) * 5 + count($metadataMatches) * 2 + count($contentMatches);

            if ($score > 0) {
                // Prefer snippet from title/keywords/metadata if found, otherwise content
                $snippetPosition = 0;
                if (count($titleMatches) > 0) {
                    $snippetPosition = $titleMatches[0];
                    $snippetContent = $page['title'];
                } elseif (count($keywordMatches) > 0) {
                    $snippetPosition = $keywordMatches[0];
                    $snippetContent = $page['keywords'];
                } elseif (count($metadataMatches) > 0) {
                    $snippetPosition = $metadataMatches[0];
                    $snippetContent = $page['metadata'];
                } else {
                    $snippetPosition = $contentMatches[0];
                    $snippetContent = $page['content'];
                }

                $results[] = [
                    'title'   => $page['title'],
                    'slug'    => $page['slug'],
                    'route'   => $page['route'],
                    'snippet' => $this->generateSnippet($snippetContent, $snippetPosition),
                    'matches' => $score,
                    'version' => $page['version'],
                ];
            }
        }

        // Sort by weighted score (descending)
        usort($results, fn ($a, $b) => $b['matches'] <=> $a['matches']);

        $maxResults = $this->config->get('learn.search.max_results', 1000);
        return array_slice($results, 0, $maxResults);
    }

    /**
     * Search for plain text matches (case-insensitive).
     *
     * @param string $query
     * @param string $content
     *
     * @return array<int, int> Array of match positions
     */
    protected function searchPlain(string $query, string $content): array
    {
        $matches = [];
        $offset = 0;
        $queryLower = mb_strtolower($query);
        $contentLower = mb_strtolower($content);

        while (($pos = mb_strpos($contentLower, $queryLower, $offset)) !== false) {
            $matches[] = $pos;
            $offset = $pos + 1;
        }

        return $matches;
    }

    /**
     * Search for wildcard pattern matches.
     *
     * @param string $regex Pre-compiled regex pattern
     * @param string $content
     *
     * @return array<int, int> Array of match positions
     */
    protected function searchWithWildcard(string $regex, string $content): array
    {
        $matches = [];

        // Split content into words and check each word
        $words = preg_split('/\s+/', $content);
        $offset = 0;

        if ($words === false) {
            // Log error if needed in the future, but for now just return empty
            return $matches;
        }

        foreach ($words as $word) {
            if (preg_match($regex, $word)) {
                $matches[] = $offset;
            }
            $offset += mb_strlen($word) + 1; // +1 for space
        }

        return $matches;
    }

    /**
     * Generate a snippet of text around a match position.
     *
     * @param string $content       Full content
     * @param int    $matchPosition Position of the match
     *
     * @return string Snippet with context
     */
    protected function generateSnippet(string $content, int $matchPosition): string
    {
        $contextLength = $this->config->get('learn.search.snippet_length', 150);

        // Calculate start and end positions
        $start = max(0, $matchPosition - $contextLength);
        $end = min(mb_strlen($content), $matchPosition + $contextLength);

        // Extract snippet
        $snippet = mb_substr($content, $start, $end - $start);

        // Add ellipsis if we're not at the beginning/end
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($end < mb_strlen($content)) {
            $snippet .= '...';
        }

        return $snippet;
    }

    /**
     * Get the cache key for search results.
     *
     * @param string $query
     * @param string $version
     * @param int    $page
     * @param int    $perPage
     *
     * @return string
     */
    protected function getResultsCacheKey(string $query, string $version, int $page, int $perPage): string
    {
        $keyFormat = $this->config->get('learn.search.results_cache_key', 'learn.search-results.%1$s.%2$s.%3$s.%4$s');
        return sprintf($keyFormat, md5($query), $version, $page, $perPage);
    }

    /**
     * Get the search index for a specific version from cache.
     *
     * @param string $version
     *
     * @return array<int, array{title: string, slug: string, route: string, content: string, version: string, keywords: string, metadata: string}>
     */
    protected function getIndex(string $version): array
    {
        $keyFormat = $this->config->get('learn.index.key', 'learn.search-index.%1$s');
        $cacheKey = sprintf($keyFormat, $version);

        $index = $this->cache->get($cacheKey);

        // Ensure we return an array even if cache returns null or unexpected type
        if (!is_array($index)) {
            return [];
        }

        return $index;
    }
}
