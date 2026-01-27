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
use InvalidArgumentException;
use UserFrosting\Config\Config;

/**
 * Service for searching documentation pages.
 *
 * Performs searches against the indexed documentation content with:
 * - Wildcard pattern matching
 * - Snippet extraction with context
 * - Result caching
 */
class SearchService
{
    /** @var int Score multiplier for title matches */
    protected const SCORE_TITLE = 10;

    /** @var int Score multiplier for keyword matches */
    protected const SCORE_KEYWORDS = 5;

    /** @var int Score multiplier for metadata matches */
    protected const SCORE_METADATA = 2;

    /** @var int Score multiplier for content matches */
    protected const SCORE_CONTENT = 1;

    public function __construct(
        protected Cache $cache,
        protected Config $config,
    ) {
    }

    /**
     * Perform the actual search and generate results with snippets.
     * Public method for use by SearchSprunje.
     *
     * @param string                  $query
     * @param array<int, IndexedPage> $index
     *
     * @return array<int, SearchResult>
     */
    public function performSearch(string $query, array $index): array
    {
        $query = trim($query);

        // Validate query length
        $minLength = $this->config->getInt('learn.search.min_length', 3);
        if ($query === '' || mb_strlen($query) < $minLength) {
            throw new InvalidArgumentException("Query must be at least {$minLength} characters long");
        }

        $hasWildcards = str_contains($query, '*') || str_contains($query, '?');
        $wildcardRegex = $hasWildcards ? $this->buildWildcardRegex($query) : null;

        $results = [];
        foreach ($index as $page) {
            $matches = $this->searchInPage($page, $query, $wildcardRegex);
            $score = $this->calculateScore($matches);

            if ($score > 0) {
                $results[] = $this->createSearchResult($page, $matches, $score);
            }
        }

        // Sort by weighted score (descending)
        usort($results, fn ($a, $b) => $b->score <=> $a->score);

        $maxResults = $this->config->get('learn.search.max_results', 1000);

        return array_slice($results, 0, $maxResults);
    }

    /**
     * Build wildcard regex pattern from query.
     *
     * @param string $query
     *
     * @return string
     */
    protected function buildWildcardRegex(string $query): string
    {
        $pattern = preg_quote($query, '/');
        $pattern = str_replace(['\*', '\?'], ['.*', '.'], $pattern);

        return '/' . $pattern . '/i';
    }

    /**
     * Search in all page fields.
     *
     * @param IndexedPage $page
     * @param string      $query
     * @param string|null $wildcardRegex
     *
     * @return array<string, array<int, int>>
     */
    protected function searchInPage(IndexedPage $page, string $query, ?string $wildcardRegex): array
    {
        if ($wildcardRegex !== null) {
            return [
                'title'    => $this->searchWithWildcard($wildcardRegex, $page->title),
                'keywords' => $this->searchWithWildcard($wildcardRegex, $page->keywords),
                'metadata' => $this->searchWithWildcard($wildcardRegex, $page->metadata),
                'content'  => $this->searchWithWildcard($wildcardRegex, $page->content),
            ];
        }

        return [
            'title'    => $this->searchPlain($query, $page->title),
            'keywords' => $this->searchPlain($query, $page->keywords),
            'metadata' => $this->searchPlain($query, $page->metadata),
            'content'  => $this->searchPlain($query, $page->content),
        ];
    }

    /**
     * Calculate weighted score from matches.
     *
     * @param array<string, array<int, int>> $matches
     *
     * @return int
     */
    protected function calculateScore(array $matches): int
    {
        return count($matches['title']) * self::SCORE_TITLE
            + count($matches['keywords']) * self::SCORE_KEYWORDS
            + count($matches['metadata']) * self::SCORE_METADATA
            + count($matches['content']) * self::SCORE_CONTENT;
    }

    /**
     * Create search result with snippet.
     *
     * @param IndexedPage                    $page
     * @param array<string, array<int, int>> $matches
     * @param int                            $score
     *
     * @return SearchResult
     */
    protected function createSearchResult(IndexedPage $page, array $matches, int $score): SearchResult
    {
        // Determine best snippet source by priority
        $snippetData = $this->selectSnippetSource($page, $matches);

        return new SearchResult(
            title: $page->title,
            slug: $page->slug,
            route: $page->route,
            snippet: $this->generateSnippet($snippetData['content'], $snippetData['position']),
            score: $score,
            version: $page->version,
        );
    }

    /**
     * Select the best snippet source from matches.
     *
     * @param IndexedPage                    $page
     * @param array<string, array<int, int>> $matches
     *
     * @return array{content: string, position: int}
     */
    protected function selectSnippetSource(IndexedPage $page, array $matches): array
    {
        $priority = [
            'title'    => $page->title,
            'keywords' => $page->keywords,
            'metadata' => $page->metadata,
            'content'  => $page->content,
        ];

        foreach ($priority as $field => $content) {
            if (isset($matches[$field]) && count($matches[$field]) > 0) {
                return ['content' => $content, 'position' => $matches[$field][0]];
            }
        }

        return ['content' => '', 'position' => 0];
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
     * @param string $regex   Pre-compiled regex pattern
     * @param string $content
     *
     * @return array<int, int> Array of match positions
     */
    protected function searchWithWildcard(string $regex, string $content): array
    {
        $matches = [];

        // Split content into words and check each word (default to empty array if preg_split fails)
        // @phpstan-ignore-next-line : preg_split can return false, but only if an error occurs, which we can ignore here.
        $words = preg_split('/\s+/', $content) ?: [];
        $offset = 0;

        foreach ($words as $word) {
            if (preg_match($regex, $word) === 1) {
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
        $start = (int) max(0, $matchPosition - $contextLength);
        $end = (int) min(mb_strlen($content), $matchPosition + $contextLength);

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
}
