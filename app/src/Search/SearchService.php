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
 */
class SearchService
{
    /** @var int Default number of characters to show in snippet context */
    protected const SNIPPET_CONTEXT_LENGTH = 150;

    /** @var int Maximum number of results to return */
    protected const MAX_RESULTS = 1000;

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
    public function search(string $query, ?string $version = null, int $page = 1, int $perPage = 10): array
    {
        // Get the version to search
        $versionId = $version ?? $this->config->get('learn.versions.latest', '6.0');

        // Get the index from cache
        $index = $this->getIndex($versionId);

        if (empty($index)) {
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

        return [
            'rows'           => $paginatedResults,
            'count'          => count($index),
            'count_filtered' => $totalResults,
        ];
    }

    /**
     * Perform the actual search and generate results with snippets.
     *
     * @param string                                                                                   $query
     * @param array<int, array{title: string, slug: string, route: string, content: string, version: string}> $index
     *
     * @return array<int, array{title: string, slug: string, route: string, snippet: string, matches: int, version: string}>
     */
    protected function performSearch(string $query, array $index): array
    {
        $results = [];
        $query = trim($query);

        if (empty($query)) {
            return $results;
        }

        // Determine if query contains wildcards
        $hasWildcards = str_contains($query, '*') || str_contains($query, '?');

        foreach ($index as $page) {
            $matches = [];

            if ($hasWildcards) {
                // Use wildcard matching
                $matches = $this->searchWithWildcard($query, $page['content']);
            } else {
                // Use simple case-insensitive search
                $matches = $this->searchPlain($query, $page['content']);
            }

            if (!empty($matches)) {
                $results[] = [
                    'title'   => $page['title'],
                    'slug'    => $page['slug'],
                    'route'   => $page['route'],
                    'snippet' => $this->generateSnippet($page['content'], $matches[0]),
                    'matches' => count($matches),
                    'version' => $page['version'],
                ];
            }
        }

        // Sort by number of matches (descending)
        usort($results, fn ($a, $b) => $b['matches'] <=> $a['matches']);

        return array_slice($results, 0, self::MAX_RESULTS);
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
     * @param string $pattern Pattern with wildcards (* and ?)
     * @param string $content
     *
     * @return array<int, int> Array of match positions
     */
    protected function searchWithWildcard(string $pattern, string $content): array
    {
        $matches = [];

        // Convert wildcard pattern to regex
        // Escape special regex characters except * and ?
        $regex = preg_quote($pattern, '/');
        $regex = str_replace(['\*', '\?'], ['.*', '.'], $regex);
        $regex = '/' . $regex . '/i'; // Case-insensitive

        // Split content into words and check each word
        $words = preg_split('/\s+/', $content);
        $offset = 0;

        if ($words === false) {
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
        $contextLength = self::SNIPPET_CONTEXT_LENGTH;

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
     * Get the search index for a specific version from cache.
     *
     * @param string $version
     *
     * @return array<int, array{title: string, slug: string, route: string, content: string, version: string}>
     */
    protected function getIndex(string $version): array
    {
        $keyFormat = $this->config->get('learn.cache.key', '%s.%s');
        $cacheKey = sprintf($keyFormat, 'search-index', $version);

        $index = $this->cache->get($cacheKey);

        return is_array($index) ? $index : [];
    }
}
